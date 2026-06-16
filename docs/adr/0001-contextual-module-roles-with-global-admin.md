# Contextual module roles with global admin

We use a hybrid authorization model: global `admin` for platform-wide operations, and module-local roles (`instructor`, `student`) on `module_memberships` for teaching workflows. This keeps module authority contextual, avoids over-granting global privileges, and preserves clear boundaries between cross-platform control and module-scoped permissions.
