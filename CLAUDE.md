# CLAUDE.md

@AGENTS.md

## Claude Code notes

- **Where guidance goes:** put guidance that applies to all agents in `AGENTS.md`. Keep only Claude Code-specific notes in this file.
- **Area rules:** Claude Code doesn't attach `.cursor/rules/*.mdc` automatically. Before editing an area, read the rule listed for it in the "Where to find more" table in `AGENTS.md`.
- **Skills** in `.claude/skills/`: `add-endpoint`, `run-checks` and `port-cross-sdk-feature`. Use `run-checks` before reporting a code change as done.
- **Permissions** (`.claude/settings.json`):
  - Pre-approved: PHPUnit, PHPStan, PHPCS and read-only git commands.
  - Blocked: reading `.env.dev`, `.env.test` and `.env.prod`, and editing `vendor/`.
  - Personal overrides go in `.claude/settings.local.json` and personal notes in `CLAUDE.local.md`; both are git-ignored.
- **PubNub MCP server:** `.mcp.json` defines it at project scope. Approve it when Claude Code asks, then run `/mcp`, select `pubnub` and authenticate.
- **Shell:** the commands in these docs use `php vendor/bin/...`, which works in both bash and PowerShell. The Composer scripts (`composer test`, `composer lint`) need bash.
