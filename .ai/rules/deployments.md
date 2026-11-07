# Deployment Rules

- **Do Not Push or Deploy Directly to Live:** Never push commits to the remote repository (`origin main`) or deploy changes to the live Hostinger production server (`gymdeskhub.com`) automatically.
- **Explicit User Approval Required:** Only commit and test locally. Wait for the user to explicitly say "deploy" or "push to live" before executing any `git push` or SSH/SCP commands to production.

