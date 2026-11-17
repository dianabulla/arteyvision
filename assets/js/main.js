document.addEventListener('DOMContentLoaded', () => {
  const donationForm = document.getElementById('donationForm');
  const donationFeedback = document.getElementById('donationFeedback');

  if (donationForm) {
    donationForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      const formData = new FormData(donationForm);
      donationFeedback.textContent = 'Enviando...';
      donationFeedback.className = 'feedback';

      try {
        const response = await fetch(`${window.APP_BASE || ''}/api/donations_create.php`, {
          method: 'POST',
          body: formData
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'No fue posible registrar la donacion.');
        }

        donationForm.reset();
        donationFeedback.textContent = payload.message;
        donationFeedback.classList.add('ok');
      } catch (error) {
        donationFeedback.textContent = error.message;
        donationFeedback.classList.add('error');
      }
    });
  }

  const revealItems = document.querySelectorAll('.reveal');
  if (revealItems.length > 0) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    revealItems.forEach((item) => observer.observe(item));
  }
});
