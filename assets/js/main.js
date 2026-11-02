document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Navigation Drawer Toggle
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const mobileDrawer = document.querySelector('.mobile-drawer');

  if (menuToggle && mobileDrawer) {
    menuToggle.addEventListener('click', () => {
      const isOpen = mobileDrawer.classList.toggle('open');
      menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (e) => {
      if (!menuToggle.contains(e.target) && !mobileDrawer.contains(e.target)) {
        mobileDrawer.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });

    // Close mobile drawer when any navigation link inside it is clicked
    mobileDrawer.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        mobileDrawer.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
      });
    });

    // Close drawer or active modal on Escape key press
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        mobileDrawer.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.querySelectorAll('.modal-backdrop.open').forEach((m) => {
          m.classList.remove('open');
        });
      }
    });
  }

  // 2. FAQ Cards Interactive Toggle
  const faqCards = document.querySelectorAll('.faq-card');
  faqCards.forEach((card) => {
    card.addEventListener('click', () => {
      card.classList.toggle('collapsed');
    });
  });

  // 3. Category Filtering (News & Gallery)
  const filterBtns = document.querySelectorAll('[data-filter-btn]');
  const filterItems = document.querySelectorAll('[data-category]');

  if (filterBtns.length > 0 && filterItems.length > 0) {
    filterBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-filter-btn');
        filterBtns.forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');

        filterItems.forEach((item) => {
          const cats = item.getAttribute('data-category') || '';
          if (target === 'all' || cats.split(' ').includes(target)) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  }

  // 4. Gallery Lightbox Modal
  const galleryTriggers = document.querySelectorAll('[data-lightbox-src]');
  const lightboxModal = document.getElementById('lightbox-modal');
  if (galleryTriggers.length > 0 && lightboxModal) {
    const lbImg = lightboxModal.querySelector('#lightbox-img');
    const lbTitle = lightboxModal.querySelector('#lightbox-title');
    const lbDesc = lightboxModal.querySelector('#lightbox-desc');
    const lbClose = lightboxModal.querySelector('.modal-close');

    galleryTriggers.forEach((item) => {
      item.addEventListener('click', () => {
        const src = item.getAttribute('data-lightbox-src');
        const title = item.getAttribute('data-lightbox-title') || '';
        const desc = item.getAttribute('data-lightbox-desc') || '';
        if (lbImg) lbImg.src = src;
        if (lbTitle) lbTitle.textContent = title;
        if (lbDesc) lbDesc.textContent = desc;
        lightboxModal.classList.add('open');
      });
    });

    if (lbClose) {
      lbClose.addEventListener('click', () => lightboxModal.classList.remove('open'));
    }
    lightboxModal.addEventListener('click', (e) => {
      if (e.target === lightboxModal) lightboxModal.classList.remove('open');
    });
  }

  // 5. Generic Modal Open/Close Triggers (Apply Online, News Reader, Legal)
  const modalTriggers = document.querySelectorAll('[data-modal-target]');
  modalTriggers.forEach((trigger) => {
    trigger.addEventListener('click', (e) => {
      const targetId = trigger.getAttribute('data-modal-target');
      const modal = document.getElementById(targetId);
      if (modal) {
        e.preventDefault();
        modal.classList.add('open');
      }
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
    const closeBtn = backdrop.querySelector('.modal-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => backdrop.classList.remove('open'));
    }
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) backdrop.classList.remove('open');
    });
  });

  // 6. Interactive Forms (Contact, Tour Booking, Online Admissions)
  const interactiveForms = document.querySelectorAll('form[data-interactive-form]');
  interactiveForms.forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const statusBox = form.querySelector('.form-status-msg');
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.textContent : 'Submit';

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
      }

      setTimeout(() => {
        if (statusBox) {
          statusBox.style.display = 'block';
          statusBox.innerHTML =
            '<strong>Thank you!</strong> Your submission has been received by the Seeds of Knowledge Intl School Admissions & Administration team. We will contact you within 24 hours.';
          statusBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        form.reset();
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      }, 450);
    });
  });
});
