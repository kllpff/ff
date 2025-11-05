# Push FF Framework to GitHub

Follow these steps to push the project to your GitHub repository.

## Initialize Git Repository

```bash
cd /Users/kirill/Projects/ff

# Initialize git
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: FF Framework setup"
```

## Add Remote Repository

```bash
# Add GitHub repository as remote
git remote add origin https://github.com/kllpff/ff.git

# Verify remote was added
git remote -v
```

## Push to GitHub

```bash
# Push to main branch (create if doesn't exist)
git branch -M main
git push -u origin main
```

## Verify Push

Visit: **https://github.com/kllpff/ff**

All files should now be visible on GitHub.

## Update Documentation Links

All links are already correctly pointing to:
- **Repository:** https://github.com/kllpff/ff
- **Documentation:** In `/docs` directory
- **Issues:** https://github.com/kllpff/ff/issues

## Clone Command for Others

Users can now clone with:

```bash
git clone https://github.com/kllpff/ff.git
cd ff
composer install
```

## Git Workflow

### Make Changes

```bash
# Edit files
# ...

# Check status
git status

# Add changes
git add .

# Commit
git commit -m "Feature: Add new feature"

# Push
git push
```

### Create Branches

```bash
# Create feature branch
git checkout -b feature/feature-name

# Make changes and commit
git add .
git commit -m "Add feature"

# Push branch
git push -u origin feature/feature-name

# Create pull request on GitHub
```

## .gitignore

The `.gitignore` file is already configured to exclude:

- `vendor/` - Composer dependencies
- `.env` - Environment variables
- `tmp/` - Temporary files
- `storage/` - User uploads
- `.DS_Store` - macOS files

These files won't be pushed to GitHub.

## FAQ

**Q: Can I change repository name?**  
A: Yes, in GitHub settings. Then update remote:
```bash
git remote set-url origin https://github.com/kllpff/new-name.git
```

**Q: Need to delete commits?**  
A: Use `git reset` or `git revert` carefully.

**Q: How to add SSH instead of HTTPS?**  
A: Change remote URL:
```bash
git remote set-url origin git@github.com:kllpff/ff.git
```

**Q: Need help with Git?**  
A: See official [Git Documentation](https://git-scm.com/doc)
