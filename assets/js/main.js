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

  // 6. Interactive Forms (Contact, Tour Booking, Online Admissions, Newsletter)
  // Connected directly to CodeIgniter backend at /inquiry/submit
  const FORM_ENDPOINT = '/inquiry/submit';

  const FORM_MESSAGES = {
    contact: {
      success:
        '<strong>Thank you!</strong> Your submission has been received by the Seeds of Knowledge Intl School Admissions &amp; Administration team. We will contact you within 24 hours.',
    },
    admission: {
      success:
        '<strong>Application received!</strong> Our Admissions Office will contact you with your assessment schedule and form reference number shortly.',
    },
    newsletter: {
      success: '<strong>Subscribed!</strong> Watch your inbox for our next newsletter.',
    },
  };
  const DEFAULT_MESSAGES = {
    success: '<strong>Thank you!</strong> Your submission has been received.',
  };
  const NOT_CONNECTED_MSG =
    '<strong>This form isn’t connected yet.</strong> Your message was not sent — please reach us directly by phone or email for now.';
  const ERROR_MSG =
    '<strong>Something went wrong.</strong> Please try again, or reach us directly by phone or email.';

  const interactiveForms = document.querySelectorAll('form[data-interactive-form]');
  interactiveForms.forEach((form) => {
    const formType = form.getAttribute('data-interactive-form') || 'contact';
    const messages = FORM_MESSAGES[formType] || DEFAULT_MESSAGES;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const statusBox = form.querySelector('.form-status-msg');
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.textContent : 'Submit';

      const showStatus = (html, isError) => {
        if (!statusBox) return;
        statusBox.classList.add('is-visible');
        statusBox.classList.toggle('is-error', isError);
        statusBox.classList.toggle('is-success', !isError);
        statusBox.innerHTML = html;
        statusBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      };

      if (!FORM_ENDPOINT) {
        showStatus(NOT_CONNECTED_MSG, true);
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
      }

      try {
        const formData = new FormData(form);
        formData.set('form_type', formType);
        formData.set('source_page', window.location.pathname);

        const response = await fetch(FORM_ENDPOINT, {
          method: 'POST',
          headers: { Accept: 'application/json' },
          body: formData,
        });

        const data = await response.json().catch(() => null);

        if (!response.ok) {
          const errDetail = data && data.message ? data.message : 'Please check your inputs and try again.';
          throw new Error(errDetail);
        }

        const successText = data && data.message ? `<strong>Thank you!</strong> ${data.message}` : messages.success;
        showStatus(successText, false);
        form.reset();
      } catch (err) {
        showStatus(`<strong>Unable to submit:</strong> ${err.message || ERROR_MSG}`, true);
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      }
    });
  });
});
