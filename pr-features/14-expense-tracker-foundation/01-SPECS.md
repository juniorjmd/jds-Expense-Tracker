# Specs

## Context

The repository is moving from a starter scaffold into a real project baseline. The user wants:

- one global git history for the whole project
- backend progress published in an independent PR
- large changes documented with clear specs, implementation notes, and acceptance criteria
- frontend direction preserved as Angular, even if Figma references came from a React export

## Scope

- normalize repository ownership so `frontend/` is managed by the root repository
- preserve reusable PHP infrastructure from the imported backend
- introduce a clean expense-tracker-oriented backend API surface
- keep the imported legacy backend materials available for reference during migration
- document the large change set for PR review

## Non-Goals

- complete Angular feature implementation in this PR
- fully remove every legacy backend artifact in this PR
- finalize all expense tracker business modules

## Target Outcomes

- root repository can version backend and frontend together
- active backend exposes clear REST endpoints for health, categories, transactions, and summary
- reviewers can evaluate the change through dedicated specs and acceptance criteria documents
