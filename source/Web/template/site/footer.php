<footer id="site-footer" class="ic-footer">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-4">
        <a class="ic-footer-brand" href="<?= Helpers::url('#top') ?>">
          <span class="ic-footer-mark"><i class="fa-solid fa-paw"></i></span>
          <span>icliv</span>
        </a>
        <p class="ic-footer-description">Tecnologia simples para negócios que cuidam de animais com presença, carinho e propósito.</p>
        <div class="ic-footer-socials">
          <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          <a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h3>icliv</h3>
        <ul>
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
          <li><a href="<?= Helpers::url('#planos') ?>">Começar agora</a></li>
          <li><a href="<?= Helpers::url('#contato') ?>">Falar com a equipe</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h3>Vamos conversar?</h3>
        <p class="ic-footer-description mb-3">Descubra como o icliv pode acompanhar a realidade do seu negócio.</p>
        <a class="ic-footer-contact" href="mailto:contato@icliv.com.br">contato@icliv.com.br <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
      </div>
    </div>

    <div class="ic-footer-bottom">
      <span>© <?= date('Y') ?> icliv. Todos os direitos reservados.</span>
      <span>Desenvolvido por <a href="https://phsa.com.br" target="_blank" rel="noopener noreferrer">PHSA Tecnologia</a></span>
    </div>
  </div>
</footer>
