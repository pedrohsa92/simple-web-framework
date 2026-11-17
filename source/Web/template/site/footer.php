<footer id="site-footer" class="sw-footer">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-4">
        <a class="sw-footer-brand" href="<?= Helpers::url('#top') ?>">
          <span class="sw-footer-mark"><i class="fa-solid fa-layer-group"></i></span>
          <span>Simple Web</span>
        </a>
        <p class="sw-footer-description">A base que não decide por você. Framework SaaS multiempresa criado pela PHSA Tecnologia.</p>
        <div class="sw-footer-socials">
          <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          <a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h3>Simple Web</h3>
        <ul>
          <li><a href="<?= Helpers::url('#sobre') ?>">Sobre</a></li>
          <li><a href="<?= Helpers::url('#proposito') ?>">Propósito</a></li>
          <li><a href="<?= Helpers::url('#recursos') ?>">Recursos</a></li>
          <li><a href="<?= Helpers::url('#como-funciona') ?>">Como funciona</a></li>
          <li><a href="<?= Helpers::url('#planos') ?>">Planos</a></li>
          <li><a href="<?= Helpers::url('#faq') ?>">Dúvidas</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h3>Acesso</h3>
        <ul>
          <li><a href="<?= Helpers::url('login') ?>">Entrar</a></li>
          <li><a href="<?= Helpers::url('#planos') ?>">Planos de demonstração</a></li>
          <li><a href="<?= Helpers::url('#contato') ?>">Falar com a PHSA</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h3>Vamos conversar?</h3>
        <p class="sw-footer-description mb-3">Tem um produto para construir? A PHSA Tecnologia pode ajudar.</p>
        <a class="sw-footer-contact" href="mailto:contato@phsa.com.br">contato@phsa.com.br <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
      </div>
    </div>

    <div class="sw-footer-bottom">
      <span>© <?= date('Y') ?> Simple Web. Todos os direitos reservados.</span>
      <span>Desenvolvido por <a href="https://phsa.com.br" target="_blank" rel="noopener noreferrer">PHSA Tecnologia</a></span>
    </div>
  </div>
</footer>
