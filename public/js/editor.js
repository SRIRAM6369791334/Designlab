(() => {
  const { designId, canvasJson, csrf } = window.DesignLab;
  const canvas = new fabric.Canvas('designCanvas', {
    preserveObjectStacking: true,
    selection: true,
  });

  const state = {
    history: [],
    redo: [],
    gridVisible: true,
    gridSize: 20,
  };

  const layersEl = document.getElementById('layers');

  function pushHistory() {
    state.history.push(JSON.stringify(canvas.toJSON(['id', 'name', 'selectable', 'visible'])));
    if (state.history.length > 100) state.history.shift();
    state.redo = [];
  }

  function restore(json) {
    canvas.loadFromJSON(json, () => {
      canvas.renderAll();
      renderLayers();
    });
  }

  function undo() {
    if (state.history.length < 2) return;
    const current = state.history.pop();
    state.redo.push(current);
    restore(state.history[state.history.length - 1]);
  }

  function redo() {
    if (!state.redo.length) return;
    const next = state.redo.pop();
    state.history.push(next);
    restore(next);
  }

  function drawGrid() {
    const { gridSize } = state;
    for (let i = 0; i < canvas.width / gridSize; i++) {
      canvas.add(new fabric.Line([i * gridSize, 0, i * gridSize, canvas.height], {
        stroke: '#ebedf3', selectable: false, evented: false, excludeFromExport: true,
      }));
    }
    for (let i = 0; i < canvas.height / gridSize; i++) {
      canvas.add(new fabric.Line([0, i * gridSize, canvas.width, i * gridSize], {
        stroke: '#ebedf3', selectable: false, evented: false, excludeFromExport: true,
      }));
    }
    canvas.sendToBack(...canvas.getObjects().filter(o => o.excludeFromExport));
  }

  function applySnap(obj) {
    const g = state.gridSize;
    obj.set({ left: Math.round((obj.left || 0) / g) * g, top: Math.round((obj.top || 0) / g) * g });
  }

  function renderLayers() {
    layersEl.innerHTML = '';
    canvas.getObjects().forEach((obj, idx) => {
      if (obj.excludeFromExport) return;
      const item = document.createElement('div');
      item.className = 'layer-item';
      const name = obj.name || `${obj.type}-${idx + 1}`;
      item.innerHTML = `
        <span contenteditable="true" data-rename="${idx}">${name}</span>
        <span>
          <button data-up="${idx}">↑</button>
          <button data-down="${idx}">↓</button>
          <button data-visible="${idx}">${obj.visible === false ? '👁️‍🗨️' : '👁️'}</button>
        </span>`;
      layersEl.appendChild(item);
    });
  }

  function activeTextMutate(mutator) {
    const obj = canvas.getActiveObject();
    if (!obj || obj.type !== 'i-text') return;
    mutator(obj);
    canvas.requestRenderAll();
    pushHistory();
  }

  async function uploadAsset(file) {
    const formData = new FormData();
    formData.append('asset', file);
    formData.append('design_id', designId);

    const response = await fetch('/api/assets', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: formData,
      credentials: 'same-origin',
    });

    if (!response.ok) throw new Error('Upload failed');
    const payload = await response.json();

    fabric.Image.fromURL(payload.optimized_url || payload.original_url, img => {
      img.set({ left: 120, top: 120, name: file.name });
      img.scaleToWidth(300);
      canvas.add(img).setActiveObject(img);
      pushHistory();
      renderLayers();
    }, { crossOrigin: 'anonymous' });
  }

  async function save(autosave = false) {
    const body = {
      canvas_json: canvas.toJSON(['id', 'name', 'selectable', 'visible']),
      autosave,
      change_note: autosave ? null : 'Saved from editor',
    };

    const response = await fetch(`/api/designs/${designId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify(body),
    });

    if (!response.ok) throw new Error('Save failed');
  }

  async function exportDesign(format) {
    const response = await fetch(`/api/designs/${designId}/exports/instant`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ format }),
    });

    if (!response.ok) throw new Error('Export failed');
    const payload = await response.json();
    window.open(payload.download_url, '_blank', 'noopener');
  }

  document.querySelectorAll('[data-shape]').forEach(btn => btn.addEventListener('click', () => {
    const shape = btn.dataset.shape;
    const fill = document.getElementById('fillColor').value;

    let obj;
    if (shape === 'rect') obj = new fabric.Rect({ width: 180, height: 120, fill, left: 200, top: 200 });
    if (shape === 'circle') obj = new fabric.Circle({ radius: 70, fill, left: 220, top: 220 });
    if (shape === 'line') obj = new fabric.Line([60, 50, 260, 50], { stroke: fill, strokeWidth: 4, left: 220, top: 220 });

    obj.name = `${shape}-${Date.now()}`;
    canvas.add(obj).setActiveObject(obj);
    pushHistory();
    renderLayers();
  }));

  document.querySelector('[data-action="draw"]').addEventListener('click', () => {
    canvas.isDrawingMode = !canvas.isDrawingMode;
    canvas.freeDrawingBrush.color = document.getElementById('fillColor').value;
    canvas.freeDrawingBrush.width = 3;
  });

  document.querySelector('[data-action="addText"]').addEventListener('click', () => {
    const fill = document.getElementById('fillColor').value;
    const fontFamily = document.getElementById('fontFamily').value;
    const fontSize = parseInt(document.getElementById('fontSize').value, 10) || 32;
    const text = new fabric.IText('Edit me', {
      left: 260, top: 260, fill, fontFamily, fontSize, charSpacing: 0, textAlign: 'left',
      name: `text-${Date.now()}`,
    });
    canvas.add(text).setActiveObject(text);
    pushHistory();
    renderLayers();
  });

  document.querySelector('[data-action="bold"]').addEventListener('click', () => activeTextMutate(obj => {
    obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold');
  }));

  document.querySelector('[data-action="italic"]').addEventListener('click', () => activeTextMutate(obj => {
    obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic');
  }));

  document.querySelector('[data-action="group"]').addEventListener('click', () => {
    const group = canvas.getActiveObject();
    if (!group || group.type !== 'activeSelection') return;
    group.toGroup();
    canvas.requestRenderAll();
    pushHistory();
    renderLayers();
  });

  document.querySelector('[data-action="ungroup"]').addEventListener('click', () => {
    const obj = canvas.getActiveObject();
    if (!obj || obj.type !== 'group') return;
    obj.toActiveSelection();
    canvas.requestRenderAll();
    pushHistory();
    renderLayers();
  });

  document.querySelector('[data-action="lock"]').addEventListener('click', () => {
    const obj = canvas.getActiveObject();
    if (!obj) return;
    obj.set({ lockMovementX: true, lockMovementY: true, lockRotation: true, lockScalingX: true, lockScalingY: true, selectable: false });
    canvas.discardActiveObject();
    canvas.requestRenderAll();
    pushHistory();
  });

  document.querySelector('[data-action="unlock"]').addEventListener('click', () => {
    const obj = canvas.getActiveObject();
    if (!obj) return;
    obj.set({ lockMovementX: false, lockMovementY: false, lockRotation: false, lockScalingX: false, lockScalingY: false, selectable: true });
    canvas.requestRenderAll();
    pushHistory();
  });

  document.querySelector('[data-action="toggleGrid"]').addEventListener('click', () => {
    state.gridVisible = !state.gridVisible;
    canvas.getObjects().filter(o => o.excludeFromExport).forEach(o => canvas.remove(o));
    if (state.gridVisible) drawGrid();
    canvas.renderAll();
  });

  document.querySelector('[data-action="undo"]').addEventListener('click', undo);
  document.querySelector('[data-action="redo"]').addEventListener('click', redo);
  document.querySelector('[data-action="save"]').addEventListener('click', () => save(false));
  document.querySelector('[data-action="autosave"]').addEventListener('click', () => save(true));
  document.querySelector('[data-action="exportPng"]').addEventListener('click', () => exportDesign('png'));
  document.querySelector('[data-action="exportSvg"]').addEventListener('click', () => exportDesign('svg'));
  document.querySelector('[data-action="exportPdf"]').addEventListener('click', () => exportDesign('pdf'));

  document.querySelector('[data-action="upload"]').addEventListener('click', () => document.getElementById('uploadInput').click());
  document.getElementById('uploadInput').addEventListener('change', e => {
    const [file] = e.target.files;
    if (file) uploadAsset(file);
  });

  layersEl.addEventListener('click', e => {
    const target = e.target;
    if (!(target instanceof HTMLElement)) return;

    const up = target.dataset.up;
    const down = target.dataset.down;
    const visible = target.dataset.visible;

    if (up !== undefined) {
      const obj = canvas.getObjects()[parseInt(up, 10)];
      canvas.bringForward(obj);
    }
    if (down !== undefined) {
      const obj = canvas.getObjects()[parseInt(down, 10)];
      canvas.sendBackwards(obj);
    }
    if (visible !== undefined) {
      const obj = canvas.getObjects()[parseInt(visible, 10)];
      obj.visible = !obj.visible;
    }

    canvas.requestRenderAll();
    pushHistory();
    renderLayers();
  });

  layersEl.addEventListener('focusout', e => {
    const target = e.target;
    if (!(target instanceof HTMLElement) || target.dataset.rename === undefined) return;
    const idx = parseInt(target.dataset.rename, 10);
    const obj = canvas.getObjects()[idx];
    if (!obj) return;
    obj.name = target.textContent?.trim() || obj.name;
    pushHistory();
  });

  canvas.on('object:moving', event => applySnap(event.target));
  canvas.on('object:added', () => renderLayers());
  canvas.on('object:modified', () => { pushHistory(); renderLayers(); });

  if (canvasJson?.objects) {
    restore(canvasJson);
  }

  if (state.gridVisible) drawGrid();
  pushHistory();
})();
