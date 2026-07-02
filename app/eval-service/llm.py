"""LiteLLM wrapper for JSON completions — adapted from Resume-Matcher."""

import json
import logging
import os
import re
import threading
from typing import Any, Literal

import litellm
from dotenv import load_dotenv
from litellm import Router
from litellm.router import RetryPolicy
from pydantic import BaseModel

load_dotenv()

litellm.drop_params = True
litellm.modify_params = True

logger = logging.getLogger(__name__)

LLM_TIMEOUT_HEALTH_CHECK = 30
LLM_TIMEOUT_COMPLETION = 120
LLM_TIMEOUT_JSON = 180

MAX_JSON_EXTRACTION_RECURSION = 10
MAX_JSON_CONTENT_SIZE = 1024 * 1024

_OPENAI_COMPATIBLE_SENTINEL = "sk-no-key"


class LLMConfig(BaseModel):
    provider: str
    model: str
    api_key: str
    api_base: str | None = None
    reasoning_effort: Literal["minimal", "low", "medium", "high"] | None = None


def get_llm_config() -> LLMConfig:
    """Load LLM config from eval-service environment variables."""
    provider = os.getenv("LLM_PROVIDER", "openai")
    model = os.getenv("LLM_MODEL", "gpt-4o-mini")
    api_key = os.getenv("OPENAI_API_KEY") or os.getenv("LLM_API_KEY") or ""
    api_base = os.getenv("LLM_API_BASE") or None
    raw_re = os.getenv("REASONING_EFFORT", "")
    reasoning_effort = raw_re if raw_re else None

    return LLMConfig(
        provider=provider,
        model=model,
        api_key=api_key,
        api_base=api_base,
        reasoning_effort=reasoning_effort,
    )


def _normalize_api_base(provider: str, api_base: str | None) -> str | None:
    if not api_base:
        return None

    base = api_base.strip()
    if not base:
        return None

    base = base.rstrip("/")

    if provider in ("openai", "openai_compatible"):
        return base or None

    if provider == "anthropic" and base.endswith("/v1"):
        base = base[: -len("/v1")].rstrip("/")

    if provider == "gemini" and base.endswith("/v1"):
        base = base[: -len("/v1")].rstrip("/")

    if provider == "openrouter" and base.endswith("/v1"):
        base = base[: -len("/v1")].rstrip("/")

    if provider == "ollama":
        for suffix in ("/v1", "/api/chat", "/api/generate", "/api"):
            if base.endswith(suffix):
                base = base[: -len(suffix)].rstrip("/")
                break

    return base or None


def _effective_api_key(provider: str, api_key: str) -> str:
    if provider == "openai_compatible" and not api_key:
        return _OPENAI_COMPATIBLE_SENTINEL
    return api_key


def _extract_text_parts(value: Any, depth: int = 0, max_depth: int = 10) -> list[str]:
    if depth >= max_depth:
        return []

    if value is None:
        return []

    if isinstance(value, str):
        return [value]

    if isinstance(value, list):
        parts: list[str] = []
        next_depth = depth + 1
        for item in value:
            parts.extend(_extract_text_parts(item, next_depth, max_depth))
        return parts

    if isinstance(value, dict):
        next_depth = depth + 1
        if "text" in value:
            return _extract_text_parts(value.get("text"), next_depth, max_depth)
        if "content" in value:
            return _extract_text_parts(value.get("content"), next_depth, max_depth)
        if "value" in value:
            return _extract_text_parts(value.get("value"), next_depth, max_depth)
        return []

    next_depth = depth + 1
    if hasattr(value, "text"):
        return _extract_text_parts(getattr(value, "text"), next_depth, max_depth)
    if hasattr(value, "content"):
        return _extract_text_parts(getattr(value, "content"), next_depth, max_depth)

    return []


def _join_text_parts(parts: list[str]) -> str | None:
    joined = "\n".join(part for part in parts if part).strip()
    return joined or None


def _safe_get(obj: Any, key: str) -> Any:
    if hasattr(obj, key):
        return getattr(obj, key)
    if isinstance(obj, dict):
        return obj.get(key)
    return None


def _extract_message_text(message: Any) -> str | None:
    content: Any = None

    if hasattr(message, "content"):
        content = message.content
    elif isinstance(message, dict):
        content = message.get("content")

    text = _join_text_parts(_extract_text_parts(content))
    if text:
        return text

    reasoning = _safe_get(message, "reasoning_content")
    text = _join_text_parts(_extract_text_parts(reasoning))
    if text:
        return text

    thinking = _safe_get(message, "thinking")
    return _join_text_parts(_extract_text_parts(thinking))


def _extract_choice_text(choice: Any) -> str | None:
    content = _extract_message_text(_safe_get(choice, "message"))
    if content:
        return content

    for field in ("text", "delta"):
        value = _safe_get(choice, field)
        if value is not None:
            extracted = _join_text_parts(_extract_text_parts(value))
            if extracted:
                return extracted

    return None


def get_model_name(config: LLMConfig) -> str:
    provider_prefixes = {
        "openai": "",
        "openai_compatible": "openai/",
        "anthropic": "anthropic/",
        "openrouter": "openrouter/",
        "gemini": "gemini/",
        "deepseek": "deepseek/",
        "groq": "groq/",
        "ollama": "ollama_chat/",
    }

    prefix = provider_prefixes.get(config.provider, "")

    if config.provider == "openrouter":
        if config.model.startswith("openrouter/"):
            return config.model
        return f"openrouter/{config.model}"

    known_prefixes = [
        "openrouter/",
        "anthropic/",
        "gemini/",
        "deepseek/",
        "groq/",
        "ollama/",
        "ollama_chat/",
        "openai/",
    ]
    if any(config.model.startswith(p) for p in known_prefixes):
        return config.model

    return f"{prefix}{config.model}" if prefix else config.model


_router: Router | None = None
_router_config_key: str = ""
_router_lock = threading.Lock()


def _config_fingerprint(config: LLMConfig) -> str:
    key_hash = hash(config.api_key) if config.api_key else 0
    return f"{config.provider}|{config.model}|{key_hash}|{config.api_base}"


def _build_router(config: LLMConfig) -> Router:
    model_name = get_model_name(config)

    litellm_params: dict[str, Any] = {"model": model_name}
    effective_key = _effective_api_key(config.provider, config.api_key)
    if effective_key:
        litellm_params["api_key"] = effective_key
    api_base = _normalize_api_base(config.provider, config.api_base)
    if api_base:
        litellm_params["api_base"] = api_base

    return Router(
        model_list=[
            {
                "model_name": "primary",
                "litellm_params": litellm_params,
            }
        ],
        num_retries=3,
        retry_policy=RetryPolicy(
            AuthenticationErrorRetries=0,
            BadRequestErrorRetries=0,
            TimeoutErrorRetries=2,
            RateLimitErrorRetries=3,
            ContentPolicyViolationErrorRetries=0,
            InternalServerErrorRetries=2,
        ),
        disable_cooldowns=True,
    )


def get_router(config: LLMConfig | None = None) -> tuple[Router, LLMConfig]:
    global _router, _router_config_key

    if config is None:
        config = get_llm_config()

    key = _config_fingerprint(config)
    with _router_lock:
        if _router is None or _router_config_key != key:
            _router = _build_router(config)
            _router_config_key = key
            logger.info("LiteLLM Router rebuilt for %s/%s", config.provider, config.model)
        router = _router

    return router, config


def _supports_json_mode(model_name: str) -> bool:
    if model_name.startswith(("ollama/", "ollama_chat/")):
        return True

    try:
        info = litellm.get_model_info(model=model_name)
        supported_params = info.get("supported_openai_params", [])
        return "response_format" in supported_params
    except Exception:
        logger.debug("Model %s not in LiteLLM registry, skipping JSON mode", model_name)
        return False


def _is_response_format_unsupported(error: Exception) -> bool:
    msg = str(error).lower()
    if "response_format" not in msg:
        return False
    rejection_cues = ("must be", "not support", "unsupported", "not allowed", "invalid")
    return any(cue in msg for cue in rejection_cues)


def _appears_truncated(data: dict, schema_type: str = "resume") -> bool:
    if not isinstance(data, dict):
        return False

    if schema_type == "resume":
        suspicious_empty_arrays = ["workExperience", "education", "skills"]
        for key in suspicious_empty_arrays:
            if key in data and data[key] == []:
                logger.warning("Possible truncation detected: '%s' is empty", key)
                return True
        return False

    if schema_type == "enrichment":
        if "items_to_enrich" not in data or "questions" not in data:
            logger.warning("Possible truncation detected: enrichment missing required keys")
            return True
        return False

    return False


def _supports_temperature(model_name: str, temperature: float | None = None) -> bool:
    if temperature is None:
        return True

    if model_name.startswith(("ollama/", "ollama_chat/")):
        return True

    try:
        info = litellm.get_model_info(model=model_name)
        supported_params = info.get("supported_openai_params", [])
        if "temperature" not in supported_params:
            return False
    except Exception:
        logger.debug("Model %s not in LiteLLM registry, skipping temperature", model_name)
        return False

    if "claude-opus-4" in model_name.lower():
        return False

    if "kimi-k2.6" in model_name.lower() and temperature != 1.0:
        return False

    return True


def _get_retry_temperature(model_name: str, attempt: int, base_temp: float = 0.1) -> float | None:
    if "kimi-k2.6" in model_name.lower():
        return 1.0

    if not _supports_temperature(model_name, base_temp):
        return None

    temperatures = [base_temp, 0.3, 0.5, 0.7]
    return temperatures[min(attempt, len(temperatures) - 1)]


def _calculate_timeout(
    operation: str,
    max_tokens: int = 4096,
    provider: str = "openai",
) -> int:
    base_timeouts = {
        "health_check": LLM_TIMEOUT_HEALTH_CHECK,
        "completion": LLM_TIMEOUT_COMPLETION,
        "json": LLM_TIMEOUT_JSON,
    }

    base = base_timeouts.get(operation, LLM_TIMEOUT_COMPLETION)
    token_factor = max(1.0, max_tokens / 4096)

    provider_factors = {
        "openai": 1.0,
        "anthropic": 1.2,
        "openrouter": 1.5,
        "groq": 1.0,
        "ollama": 2.0,
    }
    provider_factor = provider_factors.get(provider, 1.0)

    return int(base * token_factor * provider_factor)


def _strip_thinking_tags(content: str) -> str:
    stripped = re.sub(r"<think>.*?</think>", "", content, flags=re.DOTALL)
    stripped = re.sub(r"<think>.*", "", stripped, flags=re.DOTALL)
    return stripped.strip()


def _extract_json(content: str, _depth: int = 0) -> str:
    if _depth > MAX_JSON_EXTRACTION_RECURSION:
        raise ValueError(f"JSON extraction exceeded max recursion depth: {_depth}")
    if len(content) > MAX_JSON_CONTENT_SIZE:
        raise ValueError(f"Content too large for JSON extraction: {len(content)} bytes")

    original = content

    if "<think>" in content:
        content = _strip_thinking_tags(content)

    if "```json" in content:
        content = content.split("```json")[1].split("```")[0]
    elif "```" in content:
        parts = content.split("```")
        if len(parts) >= 2:
            content = parts[1]
            if content.startswith(("json", "JSON")):
                content = content[4:]

    content = content.strip()

    if content.startswith("{"):
        depth = 0
        end_idx = -1
        in_string = False
        escape_next = False

        for i, char in enumerate(content):
            if escape_next:
                escape_next = False
                continue
            if char == "\\":
                escape_next = True
                continue
            if char == '"' and not escape_next:
                in_string = not in_string
                continue
            if in_string:
                continue
            if char == "{":
                depth += 1
            elif char == "}":
                depth -= 1
                if depth == 0:
                    end_idx = i
                    break

        if end_idx == -1 and depth != 0:
            logger.warning(
                "JSON extraction found unbalanced braces (depth=%d), possible truncation",
                depth,
            )

        if end_idx != -1:
            return content[: end_idx + 1]

    start_idx = content.find("{")
    if start_idx > 0:
        return _extract_json(content[start_idx:], _depth + 1)

    logger.error(
        "Could not extract JSON from response format. Content preview: %s",
        content[:200] if content else "<empty>",
    )
    raise ValueError(f"No JSON found in response: {original[:200]}")


async def complete_json(
    prompt: str,
    system_prompt: str | None = None,
    config: LLMConfig | None = None,
    max_tokens: int = 4096,
    retries: int = 2,
    schema_type: str = "resume",
) -> dict[str, Any]:
    """Make a completion request expecting JSON response."""
    router, config = get_router(config)
    model_name = get_model_name(config)

    json_system = (
        system_prompt or ""
    ) + "\n\nYou must respond with valid JSON only. No explanations, no markdown."
    messages = [
        {"role": "system", "content": json_system},
        {"role": "user", "content": prompt},
    ]

    use_json_mode = _supports_json_mode(model_name)
    json_mode_failed = False

    for attempt in range(retries + 1):
        try:
            kwargs: dict[str, Any] = {
                "model": "primary",
                "messages": messages,
                "max_tokens": max_tokens,
                "timeout": _calculate_timeout("json", max_tokens, config.provider),
            }
            retry_temp = _get_retry_temperature(model_name, attempt)
            if retry_temp is not None:
                kwargs["temperature"] = retry_temp
            if config.reasoning_effort:
                kwargs["reasoning_effort"] = config.reasoning_effort

            if use_json_mode and not json_mode_failed:
                kwargs["response_format"] = {"type": "json_object"}

            response = await router.acompletion(**kwargs)
            content = _extract_choice_text(response.choices[0])

            if not content:
                raise ValueError("Empty response from LLM")

            logger.debug("LLM response (attempt %d): %s", attempt + 1, content[:300])

            json_str = _extract_json(content)
            result = json.loads(json_str)

            if isinstance(result, dict) and _appears_truncated(result, schema_type):
                if attempt < retries:
                    logger.warning(
                        "Parsed JSON appears truncated (attempt %d/%d), retrying",
                        attempt + 1,
                        retries + 1,
                    )
                    if schema_type == "resume":
                        hint = (
                            "\n\nIMPORTANT: Output the COMPLETE JSON object with ALL sections. Do not truncate."
                        )
                    elif schema_type == "enrichment":
                        hint = (
                            "\n\nIMPORTANT: Output the COMPLETE JSON object with ALL keys: "
                            "items_to_enrich, questions, analysis_summary. Do not truncate."
                        )
                    else:
                        hint = (
                            "\n\nIMPORTANT: Output ONLY a valid JSON object. Start with { and end with }."
                        )
                    messages[-1]["content"] = prompt + hint
                    continue
                logger.warning(
                    "Parsed JSON appears truncated on final attempt, proceeding with result"
                )

            return result

        except json.JSONDecodeError as e:
            logger.warning("JSON parse failed (attempt %d): %s", attempt + 1, e)
            if use_json_mode and not json_mode_failed:
                json_mode_failed = True
                logger.warning(
                    "JSON mode failed for %s, falling back to prompt-only (attempt %d)",
                    model_name,
                    attempt + 1,
                )
            if attempt < retries:
                messages[-1]["content"] = (
                    prompt
                    + "\n\nIMPORTANT: Output ONLY a valid JSON object. Start with { and end with }."
                )
                continue
            raise ValueError(
                f"Failed to parse JSON after {retries + 1} attempts: {e}"
            ) from e

        except ValueError as e:
            logger.warning("Content extraction failed (attempt %d): %s", attempt + 1, e)
            if attempt < retries:
                continue
            raise

        except litellm.BadRequestError as e:
            if (
                use_json_mode
                and not json_mode_failed
                and _is_response_format_unsupported(e)
            ):
                json_mode_failed = True
                logger.warning(
                    "Provider rejected response_format for %s; falling back to "
                    "prompt-only JSON mode (attempt %d)",
                    model_name,
                    attempt + 1,
                )
                if attempt < retries:
                    continue
            raise

        except Exception:
            raise

    raise ValueError(f"Failed after {retries + 1} attempts")
