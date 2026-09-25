<section class="shell page-docs">
  <header class="page-head">
    <div>
      <h1>Documentation</h1>
      <p class="muted">Ship a loading screen without editing resource code.</p>
    </div>
  </header>

  <article class="doc-block">
    <h2>Quick start</h2>
    <ol>
      <li>Create an account and open <strong>Projects</strong>.</li>
      <li>Create a project with a valid resource name (<code>^[a-zA-Z0-9_-]{1,64}$</code>).</li>
      <li>Customize in the visual editor. Changes autosave.</li>
      <li>Upload logos, backgrounds, and music in <strong>Media</strong>.</li>
      <li>Click <strong>Generate Resource</strong>, download the thin ZIP.</li>
      <li>Extract into your FiveM <code>resources</code> folder and add <code>ensure your_resource_name</code> to <code>server.cfg</code>.</li>
      <li>Players open your screen from <code>https://load.northstarscripts.us/load?t=…</code> (already set in the ZIP’s <code>fxmanifest.lua</code>).</li>
    </ol>
  </article>

  <article class="doc-block">
    <h2>Simple vs Advanced</h2>
    <ul>
      <li><strong>Simple</strong> — guided Brand → Look → Music → Extras. Ideal for first-time operators.</li>
      <li><strong>Advanced</strong> — full canvas with components, layers, snap, and inspectors.</li>
    </ul>
    <p>Pick either at signup, or toggle in the editor header / Account settings anytime.</p>
  </article>

  <article class="doc-block">
    <h2>Plans &amp; pricing</h2>
    <p><strong>Free hosted ($0)</strong> lets you try the builder with fair limits. The expensive product we’re planning is a <strong>one-time Tebex source license (~$399)</strong> — full project source for serious teams that will self-host and maintain it. Priced as a premium license on purpose. Optional hosted Standard/Pro upgrades may come later if you want us to run the cloud.</p>
  </article>

  <article class="doc-block">
    <h2>Music</h2>
    <p>Upload MP3/OGG in <strong>Media</strong>, or paste a YouTube link when your plan allows. YouTube audio plays through a <em>hidden</em> embed in the loading screen — no visible player chrome.</p>
  </article>

  <article class="doc-block">
    <h2>Plan limits (hosted)</h2>
    <ul>
      <li><strong>Free</strong> — core editor, YouTube + file music, slideshow, staff, announcements, limited components.</li>
      <li><strong>Standard · $19/mo</strong> (optional later) — higher limits + Ken Burns.</li>
      <li><strong>Pro · $39/mo</strong> (optional later) — video backgrounds, highest limits.</li>
      <li><strong>Tebex source license · ~$399 one-time</strong> (planned) — full project source on the scripts store.</li>
    </ul>
    <p>Hosted limits are enforced on save and generate, not only in the UI.</p>
  </article>

  <article class="doc-block">
    <h2>Hosted loading screens</h2>
    <p>Generated resources do <strong>not</strong> ship HTML/CSS/media locally. The ZIP is a thin FiveM resource whose <code>loadscreen</code> points at your Northstar URL:</p>
    <p><code>https://load.northstarscripts.us/load?t=YOUR_TOKEN</code></p>
    <p>Edit anytime in the builder — players see updates without regenerating the ZIP (same publish link). Media is served only for assets referenced by that project.</p>

    <h2>How generation works</h2>
    <p>Northstar assigns a stable publish token, validates your project JSON against plan limits, then packages <code>fxmanifest.lua</code> + <code>client.lua</code> into a ZIP. The live runtime is hosted under <code>/hosted/</code> and config is loaded from <code>/api/load/config</code>.</p>
  </article>

  <article class="doc-block">
    <h2>Loading progress</h2>
    <p>The runtime listens for FiveM <code>loadProgress</code>, <code>onLogLine</code>, and related message events. Percentages appear only when the game provides a real fraction. Otherwise an indeterminate status animation is shown.</p>
  </article>

  <article class="doc-block">
    <h2>Security notes</h2>
    <ul>
      <li>Downloads require login and a random build token.</li>
      <li>Uploads validate extension, MIME, and file signatures.</li>
      <li>Never expose <code>storage/</code>, <code>config/</code>, or <code>app/</code> as the web root — only <code>public/</code>.</li>
    </ul>
  </article>
</section>
