(() => {
  'use strict';

  const dropzone   = document.getElementById('drop');
  const answerFld  = document.getElementById('answerField');
  const draggables = document.querySelectorAll('.draggable');

  // Si falta algo esencial, no hacemos nada.
  if (!dropzone || !answerFld || !draggables.length) return;

  // Límite de picks: data-max-pick > window.MAX_PICK > 3
  const MAX_PICK = Number(dropzone.dataset.maxPick || window.MAX_PICK || 3);

  let picked = [];

  // Inicializa cada opción
  draggables.forEach(el => {
    const idx = String(el.dataset.idx ?? '');
    if (!idx) return;

    el.setAttribute('draggable', 'true');
    el.classList.add('cursor-move');

    // Drag & Drop
    el.addEventListener('dragstart', ev => {
      ev.dataTransfer.setData('idx', idx);
    });

    // Click / teclado para móvil y accesibilidad
    el.addEventListener('click', () => togglePick(idx));
    el.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        togglePick(idx);
      }
    });
  });

  // Área de drop
  dropzone.addEventListener('dragover', e => e.preventDefault());
  dropzone.addEventListener('drop', e => {
    e.preventDefault();
    const idx = e.dataTransfer.getData('idx');
    togglePick(idx, true);
  });

  // Añade o quita un item según su estado actual
  function togglePick(idx) {
    if (!idx) return;

    if (picked.includes(idx)) {
      removeToken(idx);
      return;
    }

    if (picked.length >= MAX_PICK) {
      // Feedback visual sutil si no queda espacio
      dropzone.classList.add('ring-2', 'ring-red-500');
      setTimeout(() => dropzone.classList.remove('ring-2', 'ring-red-500'), 300);
      return;
    }

    picked.push(idx);
    addToken(idx);
    syncAnswer();
  }

  // Crea el “token” en el dropzone
  function addToken(idx) {
    const src = document.querySelector('[data-idx="' + CSS.escape(idx) + '"]');
    if (!src) return;

    const token = src.cloneNode(true);
    token.removeAttribute('id');                 // evita IDs repetidos
    token.draggable = false;
    token.classList.remove('cursor-move');
    token.classList.add('bg-green-200', 'cursor-pointer', 'rounded', 'px-2');
    token.setAttribute('tabindex', '0');
    token.setAttribute('aria-label', 'Remove ' + (src.textContent || 'item'));
    token.dataset.idx = idx;                     // guarda el idx también en el clon

    // Quitar con click o teclado
    token.addEventListener('click', () => removeToken(idx));
    token.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === 'Backspace' || e.key === 'Delete') {
        e.preventDefault();
        removeToken(idx);
      }
    });

    dropzone.appendChild(token);
  }

  // Elimina el token y sincroniza estado
  function removeToken(idx) {
    picked = picked.filter(i => i !== idx);

    // Borra el primer token que coincida con ese idx
    const child = [...dropzone.children].find(ch => ch.dataset?.idx === idx);
    if (child) child.remove();

    syncAnswer();
  }

  // Mantiene el campo oculto sincronizado
  function syncAnswer() {
    answerFld.value = picked.join(',');
  }

  // Si el campo ya tenía valor (ej. back/forward), reconstruye tokens
  if (answerFld.value) {
    answerFld.value
      .split(',')
      .map(v => v.trim())
      .filter(Boolean)
      .forEach(idx => {
        if (!picked.includes(idx) && picked.length < MAX_PICK) {
          picked.push(idx);
          addToken(idx);
        }
      });
    syncAnswer();
  }
})();
