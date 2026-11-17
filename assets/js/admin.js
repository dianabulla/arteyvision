document.addEventListener('DOMContentLoaded', () => {
  const contentForm = document.getElementById('contentForm');
  const contentFeedback = document.getElementById('contentFeedback');

  if (contentForm && contentFeedback) {
    contentForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      const data = new FormData(contentForm);
      contentFeedback.textContent = 'Guardando...';
      contentFeedback.className = 'feedback';

      try {
        const response = await fetch(`${window.APP_BASE || ''}/api/content_save.php`, {
          method: 'POST',
          body: data
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'No fue posible guardar el contenido.');
        }

        contentFeedback.textContent = payload.message;
        contentFeedback.classList.add('ok');
      } catch (error) {
        contentFeedback.textContent = error.message;
        contentFeedback.classList.add('error');
      }
    });
  }

  const archiveForm = document.getElementById('archiveUploadForm');
  const archiveFeedback = document.getElementById('archiveFeedback');

  if (archiveForm && archiveFeedback) {
    archiveForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      const data = new FormData(archiveForm);
      archiveFeedback.textContent = 'Subiendo...';
      archiveFeedback.className = 'feedback';

      try {
        const response = await fetch(`${window.APP_BASE || ''}/api/archive_upload.php`, {
          method: 'POST',
          body: data
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'No fue posible subir el archivo.');
        }

        archiveFeedback.textContent = payload.message;
        archiveFeedback.classList.add('ok');
        window.setTimeout(() => {
          window.location.reload();
        }, 600);
      } catch (error) {
        archiveFeedback.textContent = error.message;
        archiveFeedback.classList.add('error');
      }
    });
  }

  document.querySelectorAll('.archive-delete').forEach((button) => {
    button.addEventListener('click', async () => {
      const id = Number(button.getAttribute('data-id') || 0);
      if (!id) {
        return;
      }

      if (!window.confirm('¿Eliminar este archivo del archivo interno?')) {
        return;
      }

      try {
        const response = await fetch(`${window.APP_BASE || ''}/api/archive_delete.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id,
            csrf_token: window.ARTEYVISION_CSRF || ''
          })
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'No fue posible eliminar.');
        }

        const card = button.closest('.archive-item');
        if (card) {
          card.remove();
        }
      } catch (error) {
        window.alert(error.message);
      }
    });
  });
});
