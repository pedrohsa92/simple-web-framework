





<section id="hero" class="hero-section position-relative" style="height: 70vh; overflow: hidden;">
  <div class="swiper-container h-100 w-100">
    <div class="swiper-wrapper h-100">
      <div class="swiper-slide position-relative" style="background-image: url('storage/site/temadoano.png'); background-size: cover; background-position: center;">
        <div class="overlay position-absolute w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5);"></div>
        <div class="content position-absolute text-center text-white w-100" style="top: 50%; transform: translateY(-50%);">
          <!-- <h1 class="display-5 fw-bold"></h1> -->
          <!-- <p class="lead"></p> -->
        </div>
      </div>

      <div class="swiper-slide position-relative" style="background-image: url('storage/site/iprcontagem_background.jpg'); background-size: cover; background-position: center;">
        <div class="overlay position-absolute w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5);"></div>
        <div class="content position-absolute text-center text-white w-100" style="top: 50%; transform: translateY(-50%);">
          <h1 class="display-5 fw-bold">FAÇA PARTE VOCÊ TAMBÉM</h1>
          <p class="lead">Você que sente Paz em estar conosco. E deseja se membrar na IPR Contagem. Clique no botão abaixo e estaremos te direcionando</p>
          <a href="<?= Helpers::url('#planos') ?>" class="btn btn-primary btn-lg mt-3">Conhecer o icliv</a>
        </div>
      </div>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
  </div>
</section>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    new Swiper('.swiper-container', {
      loop: true,
      effect: 'fade',
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  });
</script>

<style>
	.swiper-container {
	  width: 100vw;
	  max-width: 100%;
	  overflow: hidden;
	}

	.swiper-slide {
	  width: 100vw;
	  display: flex;
	  align-items: center;
	  justify-content: center;
	  position: relative;
	  text-align: center;
	  color: #ffffff;
	}

	.swiper-slide .overlay {
	  position: absolute;
	  top: 0;
	  left: 0;
	  width: 100%;
	  height: 100%;
	  background-color: rgba(0, 0, 0, 0.5);
	}

	.swiper-slide .content {
	  position: relative;
	  z-index: 2;
	}

	.swiper-slide h1 {
	  font-size: 3rem;
	  font-weight: bold;
	}

	.swiper-slide p {
	  font-size: 1.25rem;
	}

	.swiper-slide .btn {
	  font-size: 1rem;
	  padding: 0.75rem 1.5rem;
	  margin-top: 1rem;
	}

	@media (max-width: 768px) {
	  .swiper-slide h1 {
	    font-size: 2.1rem;
	  }

	  .swiper-slide p {
	    font-size: 1.2rem;
	  }

	  .swiper-slide .btn {
	    font-size: 0.875rem;
	    padding: 0.5rem 1rem;
	  }
	}

	@media (max-width: 576px) {
	  .swiper-slide h1 {
	    font-size: 1.5rem;
	  }

	  .swiper-slide p {
	    font-size: 1rem;
	  }

	  .swiper-slide .btn {
	    font-size: 0.8rem;
	    padding: 0.4rem 0.8rem;
	  }
	}
</style>
