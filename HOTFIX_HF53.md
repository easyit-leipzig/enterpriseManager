# HOTFIX HF53 – Self-contained project package import

HF53 fixes package imports into genuinely empty DataForm projects.

## Problem

A checked `.dfpkg` could contain DataForms, fields, bindings and relations, while a newly created target project did not yet have all DataForm metadata tables. The previous importer silently skipped missing metadata target tables. Physical application schemas and sample rows could therefore be created while the DataForms themselves were not imported. In addition, the package page built its export catalog before POST handling and did not refresh it after a successful import, so the same response could still display `Keine DataForms vorhanden`.

## Changes

- Bootstrap the DataForm metadata layer before importing `database/*.json`.
- Provision core tables for DataForms, fields, table bindings, relations and common DataForm metadata.
- Never silently skip a non-empty metadata file because its target table is missing.
- Verify that packaged DataForm IDs exist in the target after metadata import.
- Refresh the export catalog immediately after import.
- Import success message reports how many DataForms were inserted/updated.

## Expected result

Importing the `ed_ev_demo` package into a new empty DataForm project must immediately restore `Ed Ev` and `Ed Ev Info`, their table bindings, relations/lookups, physical base tables and included sample rows.
