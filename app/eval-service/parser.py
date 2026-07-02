import tempfile
from markitdown import MarkItDown
from pathlib import Path


async def parse_document(content: bytes, filename: str) -> str:
    """Convert PDF/DOCX to Markdown using markitdown.

    Args:
        content: Raw file bytes
        filename: Original filename for extension detection

    Returns:
        Markdown text content
    """
    suffix = Path(filename).suffix.lower()

    # Write to temp file for markitdown
    with tempfile.NamedTemporaryFile(suffix=suffix, delete=False) as tmp:
        tmp.write(content)
        tmp_path = Path(tmp.name)

    try:
        md = MarkItDown()
        result = md.convert(str(tmp_path))
        return result.text_content
    finally:
        tmp_path.unlink(missing_ok=True)


from pathlib import Path
from parser import parse_document
import asyncio

async def main():
    content = Path("/home/AsianTroglodyte/Documents/Karan/application stuff/Resumes/Karan Swansi Resume 1.3.0.docx.docx").read_bytes()
    text = await parse_document(content, "Karan Swansi Resume 1.3.0.docx")
    print(text[:2000])

asyncio.run(main())
