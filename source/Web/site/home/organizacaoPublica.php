<?php
  $this->layout('site::site');
  $esc = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $organizacao = is_object($organizacaoPublica ?? null) ? $organizacaoPublica : null;
  $nome = trim((string) ($organizacao->org_nomefantasia ?? '')) ?: trim((string) ($organizacao->org_razaosocial ?? 'Organização'));
  $logo = trim((string) ($organizacao->org_logo ?? ''));
  $conteudo = trim((string) ($conteudoOrganizacaoSeguro ?? ''));
  if ($conteudo === '' && trim((string) ($organizacao->org_descricao ?? '')) !== '') {
    $conteudo = '<p>' . $esc($organizacao->org_descricao) . '</p>';
  }
?>

<main class="ic-organization-public-page">
  <header class="ic-organization-public-header">
    <a href="<?= $esc(Helpers::url()) ?>" aria-label="Conheça o icliv">
      <img src="<?= $esc(Helpers::storage('storage/global/icliv.png')) ?>" alt="icliv">
    </a>
    <span><?= $esc($nome) ?></span>
  </header>

  <section class="ic-organization-public-card">
    <div class="ic-organization-public-brand">
      <?php if ($logo !== ''): ?><img src="<?= $esc(Helpers::storage($logo)) ?>" alt="Logo <?= $esc($nome) ?>"><?php else: ?><i class="fa-solid fa-paw" aria-hidden="true"></i><?php endif; ?>
    </div>
    <span class="ic-organization-public-kicker">SOBRE A ORGANIZAÇÃO</span>
    <h1><?= $esc($nome) ?></h1>
    <div class="ic-organization-public-content">
      <?= $conteudo !== '' ? $conteudo : '<p>Bem-vindo à página oficial de ' . $esc($nome) . '.</p>' ?>
    </div>
  </section>

  <footer class="ic-organization-public-footer">
    <a href="<?= $esc(Helpers::url()) ?>">Conheça a icliv <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
    <span><?= $esc($nome) ?> · Todos os direitos reservados</span>
  </footer>
</main>

<style>
  .ic-organization-public-page { min-height: 100vh; padding: 0 24px 40px; background: linear-gradient(180deg, #f1f7f4 0, #fff 430px); color: #193047; }
  .ic-organization-public-header { max-width: 1160px; height: 82px; margin: 0 auto 42px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e4eee9; }
  .ic-organization-public-header a { display: inline-flex; align-items: center; }
  .ic-organization-public-header img { width: auto; max-width: 120px; height: 38px; object-fit: contain; }
  .ic-organization-public-header span { font-weight: 700; }
  .ic-organization-public-card { max-width: 1160px; min-height: 300px; margin: 0 auto; padding: clamp(28px, 6vw, 68px); border: 1px solid #d9e9e1; border-radius: 24px; background: #fff; box-shadow: 0 18px 55px rgba(27, 67, 51, .08); }
  .ic-organization-public-brand { width: 76px; height: 76px; margin-bottom: 24px; display: grid; place-items: center; border: 1px solid #e0ebe6; border-radius: 20px; color: #27896b; background: #f6fbf8; font-size: 1.7rem; overflow: hidden; }
  .ic-organization-public-brand img { width: 100%; height: 100%; object-fit: contain; }
  .ic-organization-public-kicker { color: #248363; font-size: .75rem; font-weight: 800; letter-spacing: .12em; }
  .ic-organization-public-card h1 { margin: 10px 0 16px; color: #173d32; font-size: clamp(2rem, 5vw, 3.2rem); }
  .ic-organization-public-content { max-width: 850px; color: #465b6c; line-height: 1.8; }
  .ic-organization-public-footer { max-width: 1160px; margin: 28px auto 0; display: flex; justify-content: space-between; gap: 16px; color: #718092; font-size: .9rem; }
  .ic-organization-public-footer a { color: #248363; text-decoration: none; font-weight: 700; }
  @media (max-width: 600px) { .ic-organization-public-page { padding: 0 16px 28px; } .ic-organization-public-header { margin-bottom: 24px; } .ic-organization-public-footer { flex-direction: column; } }
</style>
