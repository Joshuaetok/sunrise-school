# Moving Seeds of Knowledge from GitHub Pages to seedsofknowledgeschool.com (Hostinger)

Goal: retire `joshuaetok.github.io/sunrise-school` as the live site and make
**seedsofknowledgeschool.com** (hosted on Hostinger, where the backend already
lives) serve these 9 marketing pages — without breaking the existing backend,
losing search ranking, or leaving placeholder content live on the real domain.

Research basis: Hostinger's own docs (hPanel File Manager, Git/GitHub App
deployment, DNS Zone Editor, SSL) current as of mid-2026, plus standard
GitHub Pages → custom domain SEO-transfer practice. Sources are linked at the
bottom of each section where it matters; a few Hostinger plan-tier details
(e.g. exactly which plans include Git auto-deploy) conflicted between
Hostinger's own docs and third-party guides, so **step 2 below has you
confirm it directly in your hPanel** before committing to a method.

---

## 0. What's actually moving

Current repo (`/storage/emulated/0/htdocs/school`), everything relative-pathed
(verified — no hardcoded `github.io` or absolute URLs anywhere in the HTML/CSS/JS),
so the files themselves need **zero code changes** to work on a new domain:

```
index.html, about.html, admissions.html, academics.html, news.html,
gallery.html, nursery-primary.html, secondary.html, contact.html
assets/css/styles.css
assets/js/main.js
assets/images/*  (34 files)
```

**Not meant to go live**, decide what to do with each before deploying:
- `screenshots/` — dev QA captures (contact page mobile/desktop). Currently tracked in git, so a Git-based deploy *would* publish them at `yourdomain.com/screenshots/...`. Harmless but sloppy on a production domain.
- `About.pdf`, `Academics.pdf`, `Admissions.pdf`, `Home.pdf` — already git-ignored (`.gitignore` has `/*.pdf`), so they will **not** be pulled by a Git deploy. If you want these as downloadable brochures on the live site, that's a deliberate separate step (upload directly, don't just untrack-ignore).
- `.nojekyll`, `.gitignore`, `.git/` — GitHub-specific / repo-only, irrelevant on Hostinger, harmless if present.

---

## 1. Pre-launch content fixes (do these *before* cutover, not after)

Going live on the real domain is the wrong time to still have placeholder
data. Fix these first — they were flagged in the earlier site review and
matter a lot more now that this is the permanent public address:

- [ ] **Real phone number.** `+234 800 000 0000` currently appears in `tel:` links on every page (hero, footer, campus cards). Replace sitewide with the real admissions line.
- [ ] **Verify the email mailboxes are live.** `info@`, `admissions@`, `billing@`, `academics@seedsofknowledgeschool.com` are used throughout. Since the domain is on Hostinger, confirm these mailboxes exist in **hPanel → Emails** (or that they forward somewhere real) before launch.
- [ ] **Real social links.** Footer Facebook/Instagram/LinkedIn/YouTube icons currently link to internal pages (`contact.html`, `gallery.html`, etc.) instead of real profiles — fix or remove before this is the official domain.
- [ ] **Confirm campus addresses** (12 Sunrise Avenue, Lekki Phase 1, Ikeja GRA) are the real ones you want published, or placeholders you're intentionally keeping for now.
- [x] **Favicon, `robots.txt`, `sitemap.xml`, Open Graph tags, `School` schema.org JSON-LD** — Built and linked across all 9 pages! Vector SVG, multi-resolution ICO, Apple Touch icon, canonical links, Open Graph, Twitter Cards, and schema markup are all in place.
- [x] **Contact/admissions/newsletter forms** — Wired in `assets/js/main.js` with structured POST submission, field names, and status feedback, ready for `FORM_ENDPOINT`.
- [x] **Deployment Packager & Automation** — Created `site-deploy.zip` packager (`build-deploy-zip.py` / `build-deploy-zip.sh`) for 1-click hPanel upload, and added `.github/workflows/deploy.yml` for automated GitHub Actions SFTP deploy. Excluded `screenshots/` from git tracking.

---

## 2. Decide how files get onto the server

Three real options. Recommendation: **A for your first test deploy today,
then switch to C (or B if available) so `git push` is the only step you ever
do again.**

| Method | Effort to set up | Ongoing effort | Needs |
|---|---|---|---|
| **A. hPanel File Manager** (manual upload/ZIP import) | Lowest | Re-upload by hand every change | Just hPanel login |
| **B. Hostinger's built-in Git deploy** (GitHub App in hPanel → Advanced → Git) | Low | Zero — push to GitHub, Hostinger auto-pulls | Plan must support it (confirm in your hPanel — availability varies by plan tier per Hostinger's own docs vs. third-party reports) |
| **C. GitHub Actions → SFTP** (workflow file in this repo) | Medium (one YAML file + secrets) | Zero — push to GitHub, Action deploys | SFTP/FTP credentials from hPanel, stored as GitHub secrets |

Confirm which of B is available to you:
1. Log into hPanel → your website → **Advanced → Git**.
2. If you see **"Connect with GitHub"**, option B works — skip to §5-B.
3. If Git isn't offered on your plan, use A now and C for the long term (§5-C).

---

## 3. Confirm the domain is actually pointed at Hostinger

Since the backend is already live on Hostinger at this domain, this is
likely already done — just verify, don't blindly change nameservers (doing
so wipes existing DNS records, including any MX records your email already
depends on).

1. In hPanel, go to **Domains → seedsofknowledgeschool.com → DNS / Nameservers**.
2. Confirm nameservers are Hostinger's (`ns1.dns-parking.com` / `ns2.dns-parking.com`) **or** confirm an A record at your external DNS already points to Hostinger's IP. Either is fine — don't switch between them without a reason.
3. Open **DNS Zone Editor** and screenshot/note the current MX records (mail) before touching anything else. You'll need these to stay intact.
4. If DNS is already live and the backend already resolves at this domain, you can skip propagation waiting — it's already propagated.

Source: [Hostinger — How to Point a Domain to Hostinger](https://www.hostinger.com/support/1863967-how-to-point-a-domain-to-hostinger), [Hostinger DNS support](https://www.hostinger.com/support/dns/).

---

## 4. Find out where the backend lives on the server (critical — do not skip)

You mentioned the backend is already hosted on Hostinger. Before uploading
anything:

1. hPanel → your website → **File Manager** → open `public_html`.
2. Note exactly what's already there. Likely one of:
   - **Empty or near-empty** `public_html` with the backend actually running as a separate app/subdomain (e.g. `api.seedsofknowledgeschool.com`) → safe to drop the marketing site straight into `public_html` root.
   - **Backend code sitting directly in `public_html` root** (e.g. a PHP app's `index.php`, config files, `/app`, `/vendor`) → do **not** overwrite. Options: (a) move backend into its own subfolder like `public_html/api/` first and update its config/any hardcoded paths, or (b) put the backend on a subdomain instead and keep marketing pages at root. This decision needs you to actually look — tell me what you see and I'll help you pick.
3. Whichever you choose, write it down now so step 5 doesn't guess wrong.

---

## 5-A. Deploy via File Manager (fastest first test)

1. On your own machine, zip the deployable files only:
   ```
   cd /storage/emulated/0/htdocs/school
   zip -r site-deploy.zip index.html about.html admissions.html academics.html \
     news.html gallery.html nursery-primary.html secondary.html contact.html assets \
     -x "assets/images/.*"
   ```
   (Adjust the exclude if you decide to leave `screenshots/` or PDFs out — they're not in this list already.)
2. hPanel → File Manager → navigate into `public_html` (or the subfolder you chose in step 4).
3. Upload `site-deploy.zip` (drag-and-drop, or the Upload button).
4. Right-click the uploaded zip → **Extract** (Hostinger's Import feature accepts `.zip` up to 256MB — this site is nowhere near that).
5. Delete the zip file afterward so it isn't publicly downloadable.
6. Confirm `index.html` ends up directly inside `public_html` (not nested one level deeper inside an extra folder from the zip) — fix the path if the extractor nested it.

Source: [Hostinger File Manager docs](https://docs.hostinger.com/websites/file-manager), [Hostinger File Manager support](https://www.hostinger.com/support/4548688-basic-actions-in-the-file-manager/).

---

## 5-B. Deploy via Hostinger's Git integration (if available on your plan)

1. hPanel → website → **Advanced → Git** → **Connect with GitHub**.
2. Authorize the Hostinger GitHub App, scope it to the `sunrise-school` repo only (least privilege).
3. Pick the branch (`main`) and the **target directory** on the server — this must be the safe location you identified in step 4, not blindly `public_html` if the backend already lives there.
4. Hostinger sets up the webhook automatically — no SSH key needed for this flow.
5. Trigger the first deploy manually (there's a "Redeploy" button) to pull `main` immediately rather than waiting for the next push.
6. From now on, every `git push origin main` auto-deploys. Nothing else to do.

Source: [Hostinger — How to Deploy a Git Repository](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/), [Hostinger Git docs](https://docs.hostinger.com/websites/git).

---

## 5-C. Deploy via GitHub Actions + SFTP (fallback if B isn't available)

1. In hPanel, get SFTP credentials: **Files → FTP Accounts** (host, port — usually 65002 for SFTP on shared hosting, username, and set a password).
2. In the GitHub repo → **Settings → Secrets and variables → Actions**, add:
   - `HOSTINGER_HOST`, `HOSTINGER_USERNAME`, `HOSTINGER_PASSWORD`, `HOSTINGER_PORT`
3. Add `.github/workflows/deploy.yml`:
   ```yaml
   name: Deploy to Hostinger
   on:
     push:
       branches: [main]
   jobs:
     deploy:
       runs-on: ubuntu-latest
       steps:
         - uses: actions/checkout@v4
         - name: Deploy via SFTP
           uses: wlixcc/SFTP-Deploy-Action@v1.2.4
           with:
             server: ${{ secrets.HOSTINGER_HOST }}
             port: ${{ secrets.HOSTINGER_PORT }}
             username: ${{ secrets.HOSTINGER_USERNAME }}
             password: ${{ secrets.HOSTINGER_PASSWORD }}
             local_path: './*'
             remote_path: '/public_html'
             sftp_only: true
             exclude: '["**/*.git*", "**/node_modules/**", "screenshots/**", "*.pdf", "link.md"]'
   ```
4. Push to `main` and watch the Action run under the repo's **Actions** tab.

(This method is community-maintained, not an official Hostinger product — test it on a non-critical path first, e.g. deploy to a `/test` subfolder before pointing it at `public_html` root.)

Source: [SFTP-Deploy-Action tutorial](https://dev.to/khalidccnu/how-to-automatic-deployment-from-github-to-server-via-sftp-github-actions-3g0p).

---

## 6. SSL

Usually automatic — confirm, don't assume:

1. hPanel → website → **Security → SSL**.
2. If it shows installed/active, done. If not, click **Install** (requires DNS already pointing at Hostinger from step 3).
3. If install fails, it's almost always DNS propagation — Hostinger's own docs say allow up to 24–48 hours after a DNS change, though since this domain is likely already live on Hostinger for the backend, SSL is probably already issued.
4. Once active, test `https://seedsofknowledgeschool.com` directly (not just `http://`) before cutover testing in step 8.

Source: [Hostinger SSL docs](https://docs.hostinger.com/websites/ssl), [How to Get an SSL Certificate at Hostinger](https://www.hostinger.com/support/1583258-how-to-install-lifetime-ssl-at-hostinger/).

---

## 7. Wire the forms to the real backend (do this once files are on the same domain)

With the marketing pages and the backend finally on the same origin,
cross-origin (CORS) headaches mostly disappear. In `assets/js/main.js`, the
submission logic already exists and is just waiting for one value:

1. Find the line: `const FORM_ENDPOINT = '';`
2. Set it to the real backend URL, e.g. `const FORM_ENDPOINT = '/api/form-submit.php';` (relative path, since same-origin) or the full URL if the backend is on a subdomain like `api.seedsofknowledgeschool.com`.
3. Tell me the backend's expected request shape (JSON body vs form-data, field names, any required auth header) — the current code sends `FormData` with a `form_type` field (`contact` / `admission` / `newsletter`) so one endpoint can branch by type, but I'll adjust to match whatever the backend actually expects.
4. Test all three forms (contact page, admissions modal, news page newsletter) end-to-end after wiring, including an intentionally-wrong submission to confirm the error state shows correctly too.

---

## 8. Test before DNS-level cutover (avoid a broken live site)

Since the domain may already be resolving to Hostinger for the backend, any
file you upload is effectively already "live" the moment it's uploaded — so
test fast and don't leave it half-broken:

1. Right after upload (§5), load `https://seedsofknowledgeschool.com` directly and click through every page, nav link, and the mobile drawer.
2. Resize the browser / test on an actual phone for the responsive breakpoints you fixed earlier.
3. Hard-refresh (`Ctrl+Shift+R`) to rule out stale cache if something looks off.
4. If Hostinger has caching (LiteSpeed Cache) enabled on the plan, purge the cache after each deploy during testing — hPanel → **Speed → Cache** → Purge.
5. Confirm the backend (whatever lives at `/api` or the subdomain) still responds correctly — this is the step that catches an accidental overwrite from step 4.

---

## 9. Retire GitHub Pages cleanly (don't just abandon it)

A bare GitHub Pages site can't send a true server-level 301 redirect, but you
can still do this properly:

1. Keep the GitHub repo and `git push` workflow exactly as-is — it's now just the *source*, deploying to Hostinger (method B or C) instead of GitHub Pages.
2. In the GitHub repo → **Settings → Pages**, either disable Pages entirely, or replace its published content with a single redirect page (`<meta http-equiv="refresh" content="0; url=https://seedsofknowledgeschool.com/">` + `<link rel="canonical" href="https://seedsofknowledgeschool.com/">`) so any old bookmarks/backlinks land on the real domain. The canonical tag matters more than the meta-refresh for SEO purposes.
3. Don't delete the old GitHub Pages immediately after cutover — leave the redirect page up for a few months while search engines re-index.

Source: [Redirecting GitHub Pages to a custom domain](https://opensource.com/article/19/7/permanently-redirect-github-pages), [Twilio — Redirect GitHub Pages to a custom domain](https://www.twilio.com/en-us/blog/redirect-github-to-custom-domain).

---

## 10. Transfer SEO signal to the new domain

1. **Google Search Console**: add `seedsofknowledgeschool.com` as a new property, verify via the DNS TXT record (hPanel → DNS Zone Editor → add the TXT record Google gives you).
2. If the old `joshuaetok.github.io/sunrise-school` property was ever verified/indexed in Search Console, use the **Change of Address** tool there to point it at the new property — this is the correct way to transfer signal from a site you can't fully 301-redirect at the server level.
3. Submit the new `sitemap.xml` (built in step 1) under **Sitemaps** in the new property.
4. Check the **Pages / Indexing** report over the following weeks for crawl errors on the new domain.
5. Update `robots.txt` to reference the new sitemap URL (`https://seedsofknowledgeschool.com/sitemap.xml`), not a GitHub Pages path.

---

## 11. Post-launch verification checklist

- [ ] All 9 pages load over `https://` with no mixed-content warnings (check browser console)
- [ ] Every internal nav link, footer link, and CTA button resolves correctly
- [ ] Mobile drawer menu opens/closes and all breakpoints still look right (from the earlier responsive fixes)
- [ ] All three forms submit successfully end-to-end
- [ ] Phone/email links use real, working contact details
- [ ] Favicon shows in the browser tab
- [ ] `robots.txt` and `sitemap.xml` are reachable at the domain root
- [ ] Existing backend functionality (whatever was already live on Hostinger) still works unchanged
- [ ] Old GitHub Pages URL redirects/canonicals to the new domain
- [ ] Search Console shows the new property verified and sitemap submitted

---

## 12. Workflow going forward

Once method B or C (§5) is in place, the day-to-day loop becomes exactly what
you're already doing: edit locally → `git commit` → `git push origin main` →
Hostinger auto-deploys. No more manual File Manager uploads, and no more
"did I remember to push" gap between GitHub and the live site like the one
that caused the first issue in this project.

---

## Open questions only you can answer (tell me and I'll adjust this plan)

1. What's currently sitting in `public_html` on Hostinger — is the backend at the root, in a subfolder, or on a separate subdomain? (step 4)
2. Does your Hostinger plan show Git integration under Advanced → Git? (step 2)
3. What does the backend's form-receiving endpoint actually expect (URL, method, field/JSON shape, auth)? (step 7)
4. Real phone number, verified mailbox status, and real social media URLs to replace the placeholders? (step 1)
