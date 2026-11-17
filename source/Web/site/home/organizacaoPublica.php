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

<main class="sw-organization-public-page">
  <header class="sw-organization-public-header">
    <a href="<?= $esc(Helpers::url()) ?>" aria-label="Conheça o Simple Web">
      <img src="<?= $esc(Helpers::storage('storage/global/logo.png')) ?>" alt="Simple Web">
    </a>
    <span><?= $esc($nome) ?></span>
  </header>

  <section class="sw-organization-public-card">
    <div class="sw-organization-public-brand">
      <?php if ($logo !== ''): ?><img src="<?= $esc(Helpers::storage($logo)) ?>" alt="Logo <?= $esc($nome) ?>"><?php else: ?><i class="fa-solid fa-building" aria-hidden="true"></i><?php endif; ?>
    </div>
    <span class="sw-organization-public-kicker">SOBRE A ORGANIZAÇÃO</span>
    <h1><?= $esc($nome) ?></h1>
    <div class="sw-organization-public-content">
      <?= $conteudo !== '' ? $conteudo : '<p>Bem-vindo à página oficial de ' . $esc($nome) . '.</p>' ?>
    </div>
  </section>

  <footer class="sw-organization-public-footer">
    <a href="<?= $esc(Helpers::url()) ?>">Conheça o Simple Web <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
    <span><?= $esc($nome) ?> · Todos os direitos reservados</span>
  </footer>
</main>

<style>
  .sw-organization-public-page { min-height: 100vh; padding: 0 24px 40px; background: linear-gradient(180deg, #f1f5f7 0, #fff 430px); color: #193047; }
  .sw-organization-public-header { max-width: 1160px; height: 82px; margin: 0 auto 42px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e4eaee; }
  .sw-organization-public-header a { display: inline-flex; align-items: center; }
  .sw-organization-public-header img { width: auto; max-width: 120px; height: 38px; object-fit: contain; }
  .sw-organization-public-header span { font-weight: 700; }
  .sw-organization-public-card { max-width: 1160px; min-height: 300px; margin: 0 auto; padding: clamp(28px, 6vw, 68px); border: 1px solid #d9e3e9; border-radius: 24px; background: #fff; box-shadow: 0 18px 55px rgba(27, 51, 67, .08); }
  .sw-organization-public-brand { width: 76px; height: 76px; margin-bottom: 24px; display: grid; place-items: center; border: 1px solid #e0e7eb; border-radius: 20px; color: #276289; background: #f6f9fb; font-size: 1.7rem; overflow: hidden; }
  .sw-organization-public-brand img { width: 100%; height: 100%; object-fit: contain; }
  .sw-organization-public-kicker { color: #245d83; font-size: .75rem; font-weight: 800; letter-spacing: .12em; }
  .sw-organization-public-card h1 { margin: 10px 0 16px; color: #172e3d; font-size: clamp(2rem, 5vw, 3.2rem); }
  .sw-organization-public-content { max-width: 850px; color: #465b6c; line-height: 1.8; }
  .sw-organization-public-footer { max-width: 1160px; margin: 28px auto 0; display: flex; justify-content: space-between; gap: 16px; color: #718092; font-size: .9rem; }
  .sw-organization-public-footer a { color: #245d83; text-decoration: none; font-weight: 700; }
  @media (max-width: 600px) { .sw-organization-public-page { padding: 0 16px 28px; } .sw-organization-public-header { margin-bottom: 24px; } .sw-organization-public-footer { flex-direction: column; } }
</style>
