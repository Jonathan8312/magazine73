# Cloud Agent Workflow

This document describes how an implementation agent should consume a Magazine73 GitHub issue and deliver a pull request.

## Preconditions

1. Read `AGENTS.md`, `docs/ai/mvp-specification.md`, and relevant files in `docs/decisions/`.
2. Confirm the issue is open and not blocked by unmerged dependencies.
3. Branch from the latest `develop`.
4. Use the branch name specified in the issue body.

## Dependency order

1. Never start an issue whose dependencies are not already merged into `develop`.
2. Use one branch and one pull request per issue.
3. Do not combine unrelated issues into a single pull request.
4. Independent issues may be worked in parallel only when they do not depend on each other.

## Implementation rules

1. Stay within the issue scope.
2. Do not modify WordPress core, themes, Docker infrastructure, or unrelated plugins.
3. Keep the main plugin file minimal and load classes with explicit `require_once`.
4. Sanitize input, escape output, and enforce capability checks for protected actions.
5. Write source strings in English and use the `magazine73` text domain.

## Validation before opening a pull request

Run the lightweight checks available in the current environment:

1. PHP syntax validation on changed PHP files.
2. PHPCS when Composer tooling is configured.
3. PHPStan when Composer tooling is configured.
4. `npm run build` when `package.json` exists.

Functional WordPress testing is performed manually by the project owner in the local Docker environment.

## Pull request requirements

Every pull request must:

1. Target `develop`.
2. Include `Closes #<issue-number>` in the description.
3. List changed files.
4. List validation results.
5. Document remaining limitations.
6. Remain unmerged.

Use `.github/pull_request_template.md` as the starting point.

## Production packaging boundaries

Development-only files must not be included in release ZIP artifacts. The repository maintains `.distignore` to document exclusions such as:

- CI and GitHub configuration
- Docker files
- Composer and npm manifests
- Internal documentation and AI instructions
- Test suites and development dependencies

Runtime plugin code, translations, generated assets, licenses, and third-party notices belong in the distributable package.

## When an issue is blocked

If an issue depends on work that is not yet merged into `develop`:

1. Skip the issue.
2. Report the exact blocker in the agent summary.
3. Continue only with currently unblocked issues.

## Completion criteria for an agent run

An agent run should stop when every currently unblocked open issue has an open pull request. Do not merge pull requests automatically.
