const API_URL = 'http://127.0.0.1:8000/index.php';

const state = {
  sprints: [],
  items: [],
  previousActions: [],
  selectedSprintId: null,
  editingSprintId: null,
  filter: 'todos'
};

const els = {
  sprintList: document.getElementById('sprintList'),
  totalSprints: document.getElementById('totalSprints'),
  selectedSprintName: document.getElementById('selectedSprintName'),
  selectedSprintDates: document.getElementById('selectedSprintDates'),
  btnNuevoSprint: document.getElementById('btnNuevoSprint'),
  btnEditarSprint: document.getElementById('btnEditarSprint'),
  btnEliminarSprint: document.getElementById('btnEliminarSprint'),
  sprintForm: document.getElementById('sprintForm'),
  sprintNombre: document.getElementById('sprintNombre'),
  fechaInicio: document.getElementById('fechaInicio'),
  fechaFin: document.getElementById('fechaFin'),
  btnCerrarModal: document.getElementById('btnCerrarModal'),
  btnCancelarModal: document.getElementById('btnCancelarModal'),
  itemForm: document.getElementById('itemForm'),
  categoria: document.getElementById('categoria'),
  fechaRevision: document.getElementById('fechaRevision'),
  descripcion: document.getElementById('descripcion'),
  btnGuardarItem: document.getElementById('btnGuardarItem'),
  itemsList: document.getElementById('itemsList'),
  totalItems: document.getElementById('totalItems'),
  previousActions: document.getElementById('previousActions'),
  tabs: document.querySelectorAll('.tab'),
  toast: document.getElementById('toast')
};

async function request(path, options = {}) {
  const response = await fetch(`${API_URL}${path}`, {
    headers: { 'Content-Type': 'application/json' },
    ...options
  });
  const result = await response.json();

  if (!response.ok || !result.ok) {
    const message = result.message || Object.values(result.errors || {}).join(' ');
    throw new Error(message || 'No se pudo completar la operación.');
  }

  return result.data;
}

function formatDate(date) {
  if (!date) return 'Sin fecha';
  return new Date(`${date}T00:00:00`).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
}

function showToast(message) {
  els.toast.textContent = message;
  els.toast.hidden = false;
  setTimeout(() => {
    els.toast.hidden = true;
  }, 2800);
}

async function loadSprints() {
  state.sprints = await request('/sprints');

  if (!state.selectedSprintId && state.sprints.length) {
    state.selectedSprintId = state.sprints[0].id;
  }

  renderSprints();
  await loadSelectedSprintData();
}

async function loadSelectedSprintData() {
  const hasSelection = Boolean(state.selectedSprintId);
  els.btnEditarSprint.disabled = !hasSelection;
  els.btnEliminarSprint.disabled = !hasSelection;
  els.btnGuardarItem.disabled = !hasSelection;

  if (!hasSelection) {
    state.items = [];
    state.previousActions = [];
    renderSelectedSprint();
    renderItems();
    renderPreviousActions();
    return;
  }

  const [items, previousActions] = await Promise.all([
    request(`/sprints/${state.selectedSprintId}/items`),
    request(`/sprints/${state.selectedSprintId}/acciones-anteriores`)
  ]);

  state.items = items;
  state.previousActions = previousActions;
  renderSelectedSprint();
  renderItems();
  renderPreviousActions();
}

function renderSprints() {
  els.totalSprints.textContent = state.sprints.length;

  if (!state.sprints.length) {
    els.sprintList.className = 'sprint-list empty';
    els.sprintList.textContent = 'No hay sprints registrados.';
    return;
  }

  els.sprintList.className = 'sprint-list';
  els.sprintList.innerHTML = state.sprints.map((sprint) => `
    <button class="sprint-card ${Number(state.selectedSprintId) === Number(sprint.id) ? 'active' : ''}" data-sprint-id="${sprint.id}">
      <strong>${escapeHtml(sprint.nombre)}</strong>
      <span>${formatDate(sprint.fecha_inicio)} - ${formatDate(sprint.fecha_fin)}</span>
    </button>
  `).join('');
}

function renderSelectedSprint() {
  const sprint = state.sprints.find((item) => Number(item.id) === Number(state.selectedSprintId));

  if (!sprint) {
    els.selectedSprintName.textContent = 'Selecciona o crea un sprint';
    els.selectedSprintDates.textContent = 'Las retrospectivas se organizan por sprint.';
    return;
  }

  els.selectedSprintName.textContent = sprint.nombre;
  els.selectedSprintDates.textContent = `${formatDate(sprint.fecha_inicio)} - ${formatDate(sprint.fecha_fin)}`;
}

function renderItems() {
  const filteredItems = state.filter === 'todos'
    ? state.items
    : state.items.filter((item) => item.categoria === state.filter);

  els.totalItems.textContent = `${filteredItems.length} item${filteredItems.length === 1 ? '' : 's'}`;

  if (!filteredItems.length) {
    els.itemsList.className = 'items-list empty';
    els.itemsList.textContent = 'No hay items registrados para este filtro.';
    return;
  }

  els.itemsList.className = 'items-list';
  els.itemsList.innerHTML = filteredItems.map(renderItemCard).join('');
}

function renderPreviousActions() {
  if (!state.previousActions.length) {
    els.previousActions.className = 'items-list empty';
    els.previousActions.textContent = 'No hay acciones anteriores para revisar.';
    return;
  }

  els.previousActions.className = 'items-list';
  els.previousActions.innerHTML = state.previousActions.map(renderItemCard).join('');
}

function renderItemCard(item) {
  const status = item.categoria === 'accion'
    ? `<span class="badge">${Number(item.cumplida) === 1 ? 'Cumplida' : 'Pendiente'}</span>`
    : '';
  const revision = item.fecha_revision ? `<span>Revisión: ${formatDate(item.fecha_revision)}</span>` : '';
  const sprint = item.sprint_nombre ? `<span>${escapeHtml(item.sprint_nombre)}</span>` : '';
  const actionButtons = item.categoria === 'accion'
    ? `<button class="mini-button" data-complete-id="${item.id}" data-complete-value="${Number(item.cumplida) === 1 ? 'false' : 'true'}">
        ${Number(item.cumplida) === 1 ? 'Marcar pendiente' : 'Marcar cumplida'}
      </button>`
    : '';

  return `
    <article class="item-card">
      <div class="item-meta">
        <span class="badge ${item.categoria}">${labelCategoria(item.categoria)}</span>
        ${status}
        ${revision}
        ${sprint}
      </div>
      <p>${escapeHtml(item.descripcion)}</p>
      <div class="item-actions">
        ${actionButtons}
        <button class="mini-button" data-delete-id="${item.id}">Eliminar</button>
      </div>
    </article>
  `;
}

function labelCategoria(categoria) {
  const labels = {
    accion: 'Acción',
    logro: 'Logro',
    impedimento: 'Impedimento',
    comentario: 'Comentario',
    otro: 'Otro'
  };

  return labels[categoria] || categoria;
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function resetSprintForm() {
  state.editingSprintId = null;
  els.sprintForm.reset();
}

els.btnNuevoSprint.addEventListener('click', () => {
  resetSprintForm();
  modal.open('Nuevo sprint');
});

els.btnEditarSprint.addEventListener('click', () => {
  const sprint = state.sprints.find((item) => Number(item.id) === Number(state.selectedSprintId));
  if (!sprint) return;

  state.editingSprintId = sprint.id;
  els.sprintNombre.value = sprint.nombre;
  els.fechaInicio.value = sprint.fecha_inicio;
  els.fechaFin.value = sprint.fecha_fin;
  modal.open('Editar sprint');
});

els.btnEliminarSprint.addEventListener('click', async () => {
  if (!state.selectedSprintId || !confirm('¿Eliminar este sprint y todos sus items?')) return;

  await request(`/sprints/${state.selectedSprintId}`, { method: 'DELETE' });
  state.selectedSprintId = null;
  showToast('Sprint eliminado.');
  await loadSprints();
});

els.sprintForm.addEventListener('submit', async (event) => {
  event.preventDefault();

  const payload = {
    nombre: els.sprintNombre.value.trim(),
    fecha_inicio: els.fechaInicio.value,
    fecha_fin: els.fechaFin.value
  };

  if (state.editingSprintId) {
    await request(`/sprints/${state.editingSprintId}`, {
      method: 'PUT',
      body: JSON.stringify(payload)
    });
    state.selectedSprintId = state.editingSprintId;
    showToast('Sprint actualizado.');
  } else {
    const created = await request('/sprints', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    state.selectedSprintId = created.id;
    showToast('Sprint creado.');
  }

  resetSprintForm();
  modal.close();
  await loadSprints();
});

els.itemForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (!state.selectedSprintId) return;

  await request(`/sprints/${state.selectedSprintId}/items`, {
    method: 'POST',
    body: JSON.stringify({
      categoria: els.categoria.value,
      descripcion: els.descripcion.value.trim(),
      fecha_revision: els.fechaRevision.value || null
    })
  });

  els.itemForm.reset();
  els.categoria.value = 'logro';
  showToast('Item registrado.');
  await loadSelectedSprintData();
});

els.sprintList.addEventListener('click', async (event) => {
  const card = event.target.closest('[data-sprint-id]');
  if (!card) return;

  state.selectedSprintId = Number(card.dataset.sprintId);
  renderSprints();
  await loadSelectedSprintData();
});

document.body.addEventListener('click', async (event) => {
  const deleteButton = event.target.closest('[data-delete-id]');
  const completeButton = event.target.closest('[data-complete-id]');

  if (deleteButton) {
    if (!confirm('¿Eliminar este item?')) return;
    await request(`/items/${deleteButton.dataset.deleteId}`, { method: 'DELETE' });
    showToast('Item eliminado.');
    await loadSelectedSprintData();
  }

  if (completeButton) {
    await request(`/items/${completeButton.dataset.completeId}/cumplida`, {
      method: 'PATCH',
      body: JSON.stringify({ cumplida: completeButton.dataset.completeValue === 'true' })
    });
    showToast('Acción actualizada.');
    await loadSelectedSprintData();
  }
});

els.tabs.forEach((tab) => {
  tab.addEventListener('click', () => {
    state.filter = tab.dataset.filter;
    els.tabs.forEach((item) => item.classList.toggle('active', item === tab));
    renderItems();
  });
});

els.btnCerrarModal.addEventListener('click', () => modal.close());
els.btnCancelarModal.addEventListener('click', () => modal.close());

loadSprints().catch((error) => {
  showToast(error.message);
});
