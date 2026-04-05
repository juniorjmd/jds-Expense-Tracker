# jds-Expense-Tracker

Starter structure for an expense tracker application built with **PHP** (backend) and **Angular** (frontend).

## Included

- `backend/`: lightweight PHP API starter with routing, request/response helpers, PDO database connection, and sample expense endpoints.
- `frontend/`: Angular starter with standalone bootstrap, routing, dashboard, transactions page, and API service.
- `database/`: starter SQL schema for users, categories, and transactions.

## Quick start

### Backend
1. Copy `backend/.env.example` to `backend/.env`
2. Update database credentials
3. Point your local server to `backend/public`
4. Import `backend/database/schema.sql`

### Frontend
1. Open a terminal inside `frontend`
2. Run `npm install`
3. Run `npm start`

## Notes

- This is a clean starter scaffold, not a finished product.
- Adjust Angular dependency versions if you want to match your local CLI version.
- The API base URL is configured in `frontend/src/app/core/services/api.config.ts`.
