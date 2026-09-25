# Northstar Scripts — email response templates

Copy-paste from `hello@northstarscripts.us` or `support@northstarscripts.us`.
Replace `[Name]` / bracketed bits before sending.

**From:** use the mailbox that matches the topic (`support@` for Load help, `hello@` for everything else).  
**Sign-off:** keep one signature block (below) on every reply.

---

## Signature

```
—
Northstar Scripts
https://northstarscripts.us/
Load: https://load.northstarscripts.us/
Store: https://northstar-scripts.tebex.store/
Discord: https://discord.gg/PASTE_YOUR_INVITE
```

---

## 1. Auto-ack (first reply while you dig in)

**Subject:** Re: [their subject]

```
Hi [Name],

Thanks for reaching out — we got your message.

We’ll get back to you as soon as we can. If this is about Northstar Load, include your account email and a short description of what you’re seeing (screenshots help).

—
Northstar Scripts
https://northstarscripts.us/
```

---

## 2. Company self-host / source license inquiry

**Subject:** Re: Self-host for your company

```
Hi [Name],

Thanks for your interest in self-hosting Northstar Load for your own company.

A few quick questions so we can quote you properly:

1. Company / community name
2. Approx. how many servers / environments you’d run
3. Do you need full source + private license terms, or hosted only is fine?
4. Any timeline?

While we talk, you’re welcome to try the free hosted builder here:
https://load.northstarscripts.us/

Private source licenses are by request only (not a public store listing). Reply here or DM us on Discord and we’ll take it from there.

—
Northstar Scripts
https://northstarscripts.us/
hello@northstarscripts.us
```

---

## 3. Self-host — quote follow-up (after they answer)

**Subject:** Re: Self-host license

```
Hi [Name],

Thanks for the details.

Based on what you shared, here’s how we’d approach a private company license for Northstar Load:

- Full project source for your company’s use
- Setup guidance for your host
- [Add price / payment method / what’s included]

If that works, reply to confirm and we’ll send next steps (invoice / Discord handoff / delivery).

—
Northstar Scripts
hello@northstarscripts.us
```

---

## 4. Load support — getting started

**Subject:** Re: Northstar Load help

```
Hi [Name],

Here’s the quick path:

1. Create an account → https://load.northstarscripts.us/register
2. Open Projects → create a project (resource name like `my_loadscreen`)
3. Customize in Simple or Advanced mode (autosaves)
4. Upload logos / backgrounds / music in Media
5. Click Generate Resource → download the ZIP
6. Extract into your FiveM `resources` folder and `ensure your_resource_name` in `server.cfg`

Players load from the hosted URL already set in the ZIP — edit anytime in the builder and they see updates without regenerating.

Docs: https://load.northstarscripts.us/docs

If something’s stuck, reply with your account email + what step failed.

—
Northstar Scripts
support@northstarscripts.us
```

---

## 5. Load support — publish / not showing in-game

**Subject:** Re: Load screen not showing

```
Hi [Name],

A few checks that usually fix this:

1. Resource is started (`ensure your_resource_name` and `refresh` / restart)
2. You’re using the ZIP from Generate Resource (not an old copy)
3. The `loadscreen` URL in `fxmanifest.lua` still points at your Northstar link (`/load?t=…`)
4. You’re signed into the same Load account that owns the project
5. Hard-refresh / clear client cache if you just republished

If it still fails, send:
- Resource name
- Account email
- Screenshot of `fxmanifest.lua` loadscreen line
- Whether other players see it or only you

—
Northstar Scripts
support@northstarscripts.us
```

---

## 6. Load support — music / YouTube

**Subject:** Re: Load music

```
Hi [Name],

You can use either:

- Upload MP3/OGG in Media and select it in the builder, or
- Paste a YouTube link (plays as a hidden embed — no player chrome)

If audio isn’t playing in-game, try a different YouTube video (some block embeds), or switch to a uploaded file.

—
Northstar Scripts
support@northstarscripts.us
```

---

## 7. Script store / Tebex purchase

**Subject:** Re: Script purchase

```
Hi [Name],

Script packages are sold on our Tebex store:
https://northstar-scripts.tebex.store/

For order / download / payment issues, use Tebex’s checkout support on that purchase — they handle billing for store products.

If you meant Northstar Load (loading-screen builder), that’s separate:
https://load.northstarscripts.us/

Happy to help with Load or point you at the right store product — just say which.

—
Northstar Scripts
hello@northstarscripts.us
```

---

## 8. General “what do you offer?”

**Subject:** Re: Northstar Scripts

```
Hi [Name],

Northstar Scripts builds FiveM tooling:

- **Scripts store** — ready-to-install packages on Tebex  
  https://northstar-scripts.tebex.store/

- **Northstar Load** — visual loading-screen builder (free hosted try-out)  
  https://load.northstarscripts.us/

Company self-host / full source for Load is private — email us or DM on Discord if that’s what you need.

—
Northstar Scripts
https://northstarscripts.us/
```

---

## 9. Recruiting / join Discord

**Subject:** Re: Joining / staff / creators

```
Hi [Name],

Thanks for wanting to help — we’re early and building in public.

Jump into Discord and introduce yourself (role you’re interested in: staff, creator, tester, or member):
https://discord.gg/PASTE_YOUR_INVITE

Looking forward to chatting there.

—
Northstar Scripts
```

---

## 10. Decline / not a fit

**Subject:** Re: [their subject]

```
Hi [Name],

Thanks for writing.

That’s outside what we offer right now. Closest options:

- Free hosted Load → https://load.northstarscripts.us/
- Scripts store → https://northstar-scripts.tebex.store/

If your needs change (especially company self-host for Load), feel free to reach out again.

—
Northstar Scripts
```

---

## 11. Closing after issue resolved

**Subject:** Re: [their subject]

```
Hi [Name],

Glad that’s sorted.

If anything else comes up with Load or the store, reply here anytime.

—
Northstar Scripts
```

---

## Quick tips

- Answer self-host from **hello@**; Load bugs from **support@** (or forward support → hello if you only have one inbox).
- Never paste publish tokens, passwords, or full `config.php` in email.
- For Tawk chat, keep the same tone — short templates above still work as chat replies.
- Replace `PASTE_YOUR_INVITE` once your Discord invite is permanent.
