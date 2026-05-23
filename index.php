<?php
// ============================================================
// index.php — Entry point. Includes logic, renders HTML.
// ============================================================
require_once 'ciphers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cipher Lab</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ══ HEADER ════════════════════════════════════════════════ -->
<header>
  <span class="logo-tag">// Classical Cryptography</span>
  <h1>Cipher Lab</h1>
  <p class="subtitle">Affine · Pigpen · Playfair — Encrypt &amp; Decrypt</p>
</header>

<!-- ══ MAIN CONTAINER ════════════════════════════════════════ -->
<div class="container">

  <!-- Tab Navigation -->
  <div class="tabs">
    <button class="tab-btn <?= $activeTab === 'affine'   ? 'active' : '' ?>" data-tab="affine">Affine</button>
    <button class="tab-btn <?= $activeTab === 'pigpen'   ? 'active' : '' ?>" data-tab="pigpen">Pigpen</button>
    <button class="tab-btn <?= $activeTab === 'playfair' ? 'active' : '' ?>" data-tab="playfair">Playfair</button>
  </div>

  <div class="card">

    <!-- ══ AFFINE PANEL ══════════════════════════════════════ -->
    <div class="cipher-panel <?= $activeTab === 'affine' ? 'active' : '' ?>"
         id="panel-affine" data-cipher="affine">

      <div class="panel-header">
        <div>
          <div style="display:flex; align-items:center; gap:10px;">
            <span class="panel-title">Affine Cipher</span>
            <span class="panel-badge">Substitution</span>
          </div>
          <p class="panel-desc">
            Encrypts using the formula
            <code style="color:var(--accent)">E(x) = (ax + b) mod 26</code>.
            The key consists of two numbers: <em>a</em> (must be coprime with 26)
            and <em>b</em> (0–25).
          </p>
        </div>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="cipher" value="affine">

        <div class="field-row cols-2">
          <div>
            <label>Key a <span style="color:#5a607a; font-size:0.65rem">(coprime w/ 26)</span></label>
            <input type="number" name="affine_a" min="1" max="25"
              value="<?= htmlspecialchars($_POST['affine_a'] ?? 1) ?>"
              placeholder="e.g. 5">
          </div>
          <div>
            <label>Key b <span style="color:#5a607a; font-size:0.65rem">(0–25)</span></label>
            <input type="number" name="affine_b" min="0" max="25"
              value="<?= htmlspecialchars($_POST['affine_b'] ?? 0) ?>"
              placeholder="e.g. 8">
          </div>
        </div>

        <div class="field-row">
          <div>
            <label>Input Text</label>
            <textarea name="input_text" placeholder="Type your message here..."><?=
              $activeTab === 'affine' ? htmlspecialchars($_POST['input_text'] ?? '') : ''
            ?></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" name="action" value="encrypt" class="btn btn-encrypt">⬆ Encrypt</button>
          <button type="submit" name="action" value="decrypt" class="btn btn-decrypt">⬇ Decrypt</button>
        </div>
      </form>

      <?php if ($activeTab === 'affine' && $output !== null): ?>
      <div class="output-box">
        <div class="output-label">
          <span><span class="dot"></span>Output</span>
          <span><?= ($_POST['action'] === 'encrypt') ? 'ENCRYPTED' : 'DECRYPTED' ?></span>
        </div>
        <div class="output-text <?= str_starts_with($output, 'Error') ? 'error' : '' ?>">
          <?= htmlspecialchars($output) ?>
        </div>
      </div>
      <?php endif; ?>
    </div><!-- /affine -->

    <!-- ══ PIGPEN PANEL ═══════════════════════════════════════ -->
    <div class="cipher-panel <?= $activeTab === 'pigpen' ? 'active' : '' ?>"
         id="panel-pigpen" data-cipher="pigpen">

      <div class="panel-header">
        <div>
          <div style="display:flex; align-items:center; gap:10px;">
            <span class="panel-title">Pigpen Cipher</span>
            <span class="panel-badge">Geometric</span>
          </div>
          <p class="panel-desc">
            A classical geometric substitution cipher that replaces each letter
            with a symbol derived from a grid or X-pattern.
            Letters are mapped to unique geometric symbols.
          </p>
        </div>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="cipher" value="pigpen">

        <div class="field-row">
          <div>
            <label>Input Text</label>
            <textarea name="input_text" placeholder="Type your message or paste symbols..."><?=
              $activeTab === 'pigpen' ? htmlspecialchars($_POST['input_text'] ?? '') : ''
            ?></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" name="action" value="encrypt" class="btn btn-encrypt">⬆ Encode</button>
          <button type="submit" name="action" value="decrypt" class="btn btn-decrypt">⬇ Decode</button>
        </div>
      </form>

      <?php if ($activeTab === 'pigpen' && $output !== null): ?>
      <div class="output-box">
        <div class="output-label">
          <span><span class="dot"></span>Output</span>
          <span><?= ($_POST['action'] === 'encrypt') ? 'ENCODED' : 'DECODED' ?></span>
        </div>
        <div class="output-text"><?= htmlspecialchars($output) ?></div>
      </div>
      <?php endif; ?>

      <hr class="divider">

      <!-- Symbol Reference Map -->
      <div class="pigpen-ref">
        <div class="pigpen-ref-title">Symbol Reference Map</div>
        <div class="pigpen-grid">
          <?php
          $pigpenRef = [
            'A'=>'⊓','B'=>'⊔','C'=>'⌐','D'=>'¬','E'=>'⊏','F'=>'⊐',
            'G'=>'⌐̣','H'=>'⊓̣','I'=>'⊔̣','J'=>'△','K'=>'▽','L'=>'▷',
            'M'=>'◁','N'=>'△̣','O'=>'▽̣','P'=>'▷̣','Q'=>'◁̣','R'=>'⊠',
            'S'=>'⊡','T'=>'⊞','U'=>'⊟','V'=>'◇','W'=>'◆',
            'X'=>'◈','Y'=>'◉','Z'=>'◊'
          ];
          foreach ($pigpenRef as $letter => $sym): ?>
          <div class="pigpen-cell">
            <span class="pigpen-sym"><?= $sym ?></span>
            <span class="pigpen-ltr"><?= $letter ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div><!-- /pigpen -->

    <!-- ══ PLAYFAIR PANEL ════════════════════════════════════ -->
    <div class="cipher-panel <?= $activeTab === 'playfair' ? 'active' : '' ?>"
         id="panel-playfair" data-cipher="playfair">

      <div class="panel-header">
        <div>
          <div style="display:flex; align-items:center; gap:10px;">
            <span class="panel-title">Playfair Cipher</span>
            <span class="panel-badge">Digraph</span>
          </div>
          <p class="panel-desc">
            Encrypts pairs of letters (digraphs) using a 5×5 key matrix.
            <em>J</em> is treated as <em>I</em>. Spaces and non-alpha characters
            are stripped before processing.
          </p>
        </div>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="cipher" value="playfair">

        <div class="field-row">
          <div>
            <label>Keyword</label>
            <input type="text" name="playfair_key"
              value="<?= htmlspecialchars($_POST['playfair_key'] ?? 'MONARCHY') ?>"
              placeholder="e.g. MONARCHY">
          </div>
        </div>

        <div class="field-row">
          <div>
            <label>Input Text</label>
            <textarea name="input_text" placeholder="Type your message..."><?=
              $activeTab === 'playfair' ? htmlspecialchars($_POST['input_text'] ?? '') : ''
            ?></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" name="action" value="encrypt" class="btn btn-encrypt">⬆ Encrypt</button>
          <button type="submit" name="action" value="decrypt" class="btn btn-decrypt">⬇ Decrypt</button>
        </div>
      </form>

      <?php if ($activeTab === 'playfair' && $output !== null): ?>
      <div class="output-box">
        <div class="output-label">
          <span><span class="dot"></span>Output</span>
          <span><?= ($_POST['action'] === 'encrypt') ? 'ENCRYPTED' : 'DECRYPTED' ?></span>
        </div>
        <div class="output-text"><?= htmlspecialchars($output) ?></div>
      </div>
      <?php endif; ?>

      <hr class="divider">

      <!-- Live Key Matrix -->
      <?php
      $pfKeyDisplay = $_POST['playfair_key'] ?? 'MONARCHY';
      $pfMatrix     = buildPlayfairMatrix($pfKeyDisplay);
      ?>
      <div class="pf-matrix-label">
        Key Matrix — <span><?= htmlspecialchars(strtoupper($pfKeyDisplay)) ?></span>
      </div>
      <div class="pf-matrix">
        <?php foreach ($pfMatrix as $cell): ?>
        <div class="pf-cell"><?= $cell ?></div>
        <?php endforeach; ?>
      </div>
    </div><!-- /playfair -->

  </div><!-- /card -->
</div><!-- /container -->

<!-- ══ FOOTER ════════════════════════════════════════════════ -->
<footer>Cipher Lab — Classical Encryption Tool · PHP &amp; CSS · No database · Stateless</footer>

<!-- ══ SCRIPTS ════════════════════════════════════════════════ -->
<script>
  // Tab switching — client-side panel toggle
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.cipher-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('panel-' + tab).classList.add('active');
    });
  });
</script>

</body>
</html>
