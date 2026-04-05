# Acceptance Criteria

- the repository has a single git history at the project root for backend and frontend work
- `frontend/.git` no longer exists
- the backend has an active bootstrap flow centered on `backend/public/index.php`
- the backend exposes route definitions for:
  - `GET /api/health`
  - `GET /api/categories`
  - `GET /api/transactions`
  - `POST /api/transactions`
  - `DELETE /api/transactions/{id}`
  - `GET /api/summary`
- the new active backend files pass `php -l`
- the PR includes explicit documentation of specs, implementation details, and acceptance criteria
