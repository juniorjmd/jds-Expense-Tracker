# jds-Expense-Tracker

Expense tracker web application built with **PHP** on the backend and **Angular** on the frontend.

The product direction is based on a Figma reference and a sample implementation originally exported in React, but this repository will be implemented with its own architecture in **Angular + Tailwind** and **PHP + MySQL**.

## Current Direction

- `backend/`: PHP API foundation using custom bootstrap, HTTP helpers, routing, PDO connection utilities, and reusable query helpers.
- `frontend/`: workspace that will host the Angular application and Tailwind-based UI implementation.
- `database/`: SQL assets for the expense tracker domain.
- `docs/`: project notes and supporting documentation.

## Goal

Build a maintainable expense tracker that supports:

- authentication
- transaction management
- categories
- monthly summaries
- dashboard metrics
- responsive UI

## Backend Notes

The backend currently preserves reusable infrastructure from a previous PHP project, but the legacy routing and module surface are being separated from the active expense-tracker API.

Use `backend/.env.example` as the reference for local environment setup.

## Frontend Notes

The frontend is being normalized under the main repository so future Angular work, Tailwind integration, and PR review happen from a single global git history.
