# Specs

## Context

The repository is no longer just establishing a foundation. It now needs to operate as the first real SaaS-ready baseline for the Expense Tracker:

- Angular frontend and PHP backend working together in one repository
- one shared database with multi-company and multi-establishment relationships
- a privileged `superusuario` role for global maintenance
- company-scoped administrators with no cross-company visibility
- explicit access and audit when the superuser inspects another company's operational detail

## Scope

- keep root repository ownership for frontend and backend together
- evolve the backend into a multi-company SaaS-oriented API surface
- add company management for superuser only
- enforce company scoping across users, establishments, templates, transactions, and summaries
- prevent the superuser from seeing cross-company operational detail by default
- require explicit company entry for that detail and audit it
- document the change set with clear PR materials

## Non-Goals

- subscription billing and plans
- tenant-specific branding
- complete audit coverage for every destructive action
- advanced reporting, filtering, or pagination

## Target Outcomes

- the application works as a single-repo Angular + PHP product
- operational data is isolated by company
- each transaction is tied to both company and establishment
- the superuser can maintain companies without automatically seeing all operational detail
- explicit superuser inspection of a company is tracked
- reviewers can evaluate the work through dedicated specs, implementation notes, and acceptance criteria
