# Laravel Design Lab (CustomInk-like) - Production Blueprint

## 1) Recommended folder structure

```txt
app/
  Http/
    Controllers/Api/
      AssetController.php
      CanvasController.php
      DesignController.php
      ExportController.php
    Middleware/
      EnsureRole.php
    Requests/
      StoreDesignRequest.php
      UpdateDesignRequest.php
      UploadAssetRequest.php
  Jobs/
    RenderExportJob.php
  Models/
    User.php
    Design.php
    DesignVersion.php
    Asset.php
  Policies/
    DesignPolicy.php
  Providers/
    AuthServiceProvider.php
    RouteServiceProvider.php
  Services/
    AssetService.php
    CanvasSanitizer.php
    ExportService.php
    VirusScanService.php
database/migrations/
routes/
  api.php
  web.php
resources/views/
  layouts/app.blade.php
  design/editor.blade.php
public/js/editor.js
```

## 2) Database schema (MySQL)

- `users`: add indexed `role` (`admin`, `user`).
- `designs`: owner, `canvas_json` (longText JSON), metadata, status, autosave timestamp.
- `design_versions`: immutable snapshots for version history.
- `assets`: original + optimized variants, MIME, SHA-256, scan state.

Use migration index strategy for high-volume filters:
- `designs(user_id, updated_at)` for dashboard listing.
- `design_versions(design_id, created_at)` for timeline UI.
- `assets(user_id, created_at)` and `assets(sha256)` for dedup/scans.

## 3) Auth, authorization, and policies

- API auth via Sanctum (`auth:sanctum`).
- Role middleware for privileged routes (`role:admin`).
- `DesignPolicy` enforces owner/admin access.
- Route model binding + policy checks in each controller action.

## 4) API endpoints (REST)

- `GET /api/designs`
- `POST /api/designs`
- `GET /api/designs/{design}`
- `PUT /api/designs/{design}`
- `DELETE /api/designs/{design}`
- `GET /api/designs/{design}/canvas`
- `POST /api/assets`
- `POST /api/designs/{design}/exports/queue`
- `POST /api/designs/{design}/exports/instant`

All routes are protected with:
- auth middleware
- API + custom upload/export throttles
- Form Request validation

## 5) Fabric.js editor capabilities included

`public/js/editor.js` implements:
- Upload PNG/JPG/SVG to `/api/assets`
- Shapes (rectangle/circle/line)
- Free draw brush
- Text insertion (font family/size/color), bold/italic toggle
- Select/move/resize/rotate via Fabric controls
- Lock/unlock objects
- Group/ungroup
- Layer panel with reorder, visibility toggle, rename
- Grid rendering + snap-to-grid
- Undo/redo (client history stack)
- Save/autosave to `/api/designs/{id}`
- Export actions (PNG/SVG/PDF)

### Transparent auto-trim note
For raster uploads, run trimming server-side (ImageMagick `-trim` or equivalent GD strategy) before optimized output. Hook this into `AssetService` pipeline.

## 6) Design persistence and versioning

`DesignController@update`:
- `autosave=true`: updates `canvas_json` + `autosaved_at` only.
- manual save: appends `design_versions` snapshot with incremented `version_number`.

This pattern gives:
- fast drafts
- immutable history
- recoverable user states

## 7) Export architecture (300 DPI + secure links)

- Queue heavy exports (`RenderExportJob`) on `exports` queue.
- Use `ExportService` with short-lived signed/temporary URLs (`Storage::temporaryUrl`).
- For true 300 DPI fidelity of Fabric designs:
  - render in headless Chromium/canvas service
  - force scale factor based on print size
  - convert to target format (PNG/SVG/PDF)

## 8) Security hardening checklist

- Strict MIME + extension validation in `UploadAssetRequest`.
- Malware scan hook in `VirusScanService` (ClamAV/external).
- Server-side sanitization for canvas payload in `CanvasSanitizer`.
- Private storage for exports; never expose raw file paths.
- CSRF token included for same-origin session calls.
- Policies for object-level authorization.
- Parameterized ORM queries only (Eloquent/query builder).

## 9) Performance strategy (100k users/month)

- Redis-backed queues + Horizon for exports, optimization, scanning.
- Read replicas for design listing/reporting workloads.
- Cache expensive design metadata summaries.
- CDN edge caching for optimized assets.
- Add async auto-save cadence (e.g., every 8–15 seconds debounce).
- Use object storage lifecycle rules for stale export artifacts.

## 10) Production operational practices

- Add audit logging for export/download events.
- Monitor queue latency + failed jobs alerting.
- Add daily DB backups and restore drills.
- Use S3 private bucket + signed URLs.
- Enforce CSP headers and secure cookies.
- Add comprehensive feature tests for policy boundaries and validation.

## 11) Required Laravel wiring

In your HTTP kernel, register role middleware alias:

```php
'role' => \App\Http\Middleware\EnsureRole::class,
```

Filesystem disks expected:
- `public` for optimized user assets (CDN frontable)
- `private` for export artifacts and restricted files

If using S3, map both disks to S3 buckets/prefixes and keep `temporaryUrl` enabled.
