@extends('layouts.app')

@section('title', 'Editor - ' . $design->name)

@section('content')
<div class="shell">
    <aside class="panel toolbar">
        <h3>Tools</h3>
        <button data-action="upload">Upload Image</button>
        <input id="uploadInput" type="file" accept=".png,.jpg,.jpeg,.svg" hidden>

        <button data-shape="rect">Rectangle</button>
        <button data-shape="circle">Circle</button>
        <button data-shape="line">Line</button>
        <button data-action="draw">Free Draw</button>

        <hr>
        <label>Font Family
            <select id="fontFamily">
                <option value="Arial">Arial</option>
                <option value="Roboto">Roboto</option>
                <option value="Montserrat">Montserrat</option>
            </select>
        </label>
        <label>Font Size <input id="fontSize" type="number" value="32"></label>
        <label>Color <input id="fillColor" type="color" value="#111111"></label>
        <button data-action="addText">Add Text</button>
        <button data-action="bold">Bold</button>
        <button data-action="italic">Italic</button>

        <hr>
        <button data-action="group">Group</button>
        <button data-action="ungroup">Ungroup</button>
        <button data-action="lock">Lock</button>
        <button data-action="unlock">Unlock</button>
        <button data-action="toggleGrid">Toggle Grid</button>
        <button data-action="undo">Undo</button>
        <button data-action="redo">Redo</button>

        <hr>
        <button data-action="save">Save</button>
        <button data-action="autosave">Auto-save Now</button>
        <button data-action="exportPng">Export PNG</button>
        <button data-action="exportSvg">Export SVG</button>
        <button data-action="exportPdf">Export PDF</button>
    </aside>

    <main id="canvas-wrap">
        <canvas id="designCanvas" width="1200" height="800"></canvas>
    </main>

    <aside class="panel">
        <h3>Layers</h3>
        <div id="layers"></div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    window.DesignLab = {
        designId: {{ $design->id }},
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        canvasJson: @json($design->canvas_json),
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script src="{{ asset('js/editor.js') }}"></script>
@endpush
