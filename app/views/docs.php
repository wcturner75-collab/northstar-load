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
      <li>Click <strong>Generate Resource</strong>, download the ZIP.</li>
      <li>Extract into your FiveM <code>resources</code> folder and add <code>ensure your_resource_name</code> to <code>server.cfg</code>.</li>
    </ol>
  </article>

  <article class="doc-block">
    <h2>How generation works</h2>
    <p>Northstar keeps one tested FiveM master runtime. Your project JSON and approved media are merged into that template, then packaged with PHP <code>ZipArchive</code>. The ZIP contains a top-level resource folder so extraction stays clean.</p>
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
