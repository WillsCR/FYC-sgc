@extends('layouts.app')

@section('title', 'No Conformidades')

@push('styles')
<style>
/* ── Variables & reset ──────────────────────────────────────────────────────── */
:root {
    --nc-navy:    #0D2B5E;
    --nc-blue:    #1d4ed8;
    --nc-muted:   #6b7280;
    --nc-border:  #e5e7eb;
    --nc-radius:  8px;
}

/* ── Page header ────────────────────────────────────────────────────────────── */
.nc-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.nc-page-header h1 {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--nc-navy);
    margin: 0;
}
.nc-page-header p {
    margin: 4px 0 0;
    font-size: .85rem;
    color: var(--nc-muted);
}

/* ── Toolbar ────────────────────────────────────────────────────────────────── */
.nc-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.nc-toolbar input[type=search] {
    padding: 7px 12px;
    border: 1px solid var(--nc-border);
    border-radius: var(--nc-radius);
    font-size: .875rem;
    width: 240px;
    outline: none;
}
.nc-toolbar input[type=search]:focus {
    border-color: var(--nc-navy);
    box-shadow: 0 0 0 3px rgba(13,43,94,.1);
}
.nc-toolbar select {
    padding: 7px 10px;
    border: 1px solid var(--nc-border);
    border-radius: var(--nc-radius);
    font-size: .875rem;
    outline: none;
    background: #fff;
    cursor: pointer;
}

/* ── Buttons ────────────────────────────────────────────────────────────────── */
.btn-import {
    background: #fff;
    color: #15803d;
    border: 1.5px solid #bbf7d0;
    border-radius: var(--nc-radius);
    padding: 8px 16px;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s, border-color .15s;
}
.btn-import:hover { background: #f0fdf4; border-color: #86efac; }

.btn-primary {
    background: var(--nc-navy);
    color: #fff;
    border: none;
    border-radius: var(--nc-radius);
    padding: 8px 16px;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: opacity .15s;
}
.btn-primary:hover  { opacity: .85; }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }
.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: var(--nc-radius);
    padding: 8px 16px;
    font-size: .875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s;
}
.btn-secondary:hover { background: #e5e7eb; }
.btn-danger {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: var(--nc-radius);
    padding: 8px 14px;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.btn-danger:hover { background: #fecaca; }
.btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 5px;
    color: var(--nc-muted);
    font-size: .9rem;
    transition: background .15s, color .15s;
    line-height: 1;
}
.btn-icon:hover          { background: #f3f4f6; color: #111; }
.btn-icon.danger:hover   { background: #fee2e2; color: #dc2626; }

/* ── Table wrapper ──────────────────────────────────────────────────────────── */
.nc-table-wrap {
    overflow-x: auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
}
.nc-table {
    width: 100%;
    min-width: 3000px;
    border-collapse: collapse;
    font-size: .8rem;
}

/* ── Group header rows ─────────────────────────────────────────────────────── */
.nc-table thead tr.th-group th {
    background: #0D2B5E;
    color: #fff;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 7px 10px;
    text-align: center;
    border: 1px solid rgba(255,255,255,.15);
    white-space: nowrap;
}
.nc-table thead tr.th-group th.th-id-group   { background: #1e3a6e; }
.nc-table thead tr.th-group th.th-inm-group  { background: #1e5c3a; }
.nc-table thead tr.th-group th.th-corr-group { background: #5c3a1e; }
.nc-table thead tr.th-group th.th-verif-group{ background: #3a1e5c; }

/* ── Sub-header row ────────────────────────────────────────────────────────── */
.nc-table thead tr.th-sub th {
    background: #f1f5f9;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #475569;
    padding: 7px 8px;
    text-align: center;
    border: 1px solid #e2e8f0;
    border-bottom: 2px solid #cbd5e1;
    white-space: nowrap;
}
.nc-table thead tr.th-sub th.sub-inm  { background: #f0fdf4; color: #15803d; }
.nc-table thead tr.th-sub th.sub-corr { background: #fffbeb; color: #92400e; }
.nc-table thead tr.th-sub th.sub-verif{ background: #faf5ff; color: #6b21a8; }

/* ── Data cells ────────────────────────────────────────────────────────────── */
.nc-table td {
    padding: 9px 10px;
    border: 1px solid #e9ecef;
    vertical-align: top;
    color: #1f2937;
    line-height: 1.45;
}
.nc-table tbody tr:hover td { background: #fafbff; }
.nc-table td.center { text-align: center; vertical-align: middle; }
.nc-table td.td-inm  { background: #f9fffe; }
.nc-table td.td-corr { background: #fffdf5; }
.nc-table td.td-verif{ background: #fdfaff; }

/* ── Column widths ─────────────────────────────────────────────────────────── */
.nc-table .col-num    { width: 46px;  min-width: 46px;  }
.nc-table .col-fecha  { width: 88px;  min-width: 88px;  white-space: nowrap; }
.nc-table .col-origen { width: 110px; min-width: 110px; }
.nc-table .col-area   { width: 130px; min-width: 130px; }
.nc-table .col-tipo   { width: 75px;  min-width: 75px;  text-align: center; }
.nc-table .col-nom    { width: 130px; min-width: 130px; }
.nc-table .col-cargo  { width: 110px; min-width: 110px; }
.nc-table .col-hall   { width: 240px; min-width: 240px; }
.nc-table .col-acc    { width: 260px; min-width: 260px; }
.nc-table .col-status { width: 90px;  min-width: 90px;  text-align: center; }
.nc-table .col-eficaz { width: 70px;  min-width: 70px;  text-align: center; }
.nc-table .col-fecha2 { width: 88px;  min-width: 88px;  white-space: nowrap; text-align: center; }
.nc-table .col-reg    { width: 120px; min-width: 120px; }
.nc-table .col-obs    { width: 150px; min-width: 150px; }
.nc-table .col-docs   { width: 70px;  min-width: 70px;  text-align: center; }
.nc-table .col-act    { width: 80px;  min-width: 80px;  text-align: center; }

/* ── Action list inside cell ───────────────────────────────────────────────── */
.acc-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.acc-list li {
    display: flex;
    gap: 6px;
    font-size: .78rem;
    line-height: 1.4;
}
.acc-list li .acc-num {
    flex-shrink: 0;
    font-weight: 700;
    color: var(--nc-navy);
    min-width: 18px;
}
.acc-list li .acc-txt { color: #374151; }
.cell-dash { color: #cbd5e1; font-size: .9rem; }

/* Badges */
.badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 600;
    white-space: nowrap;
}
.badge-pendiente  { background: #fef3c7; color: #92400e; }
.badge-resuelta   { background: #dcfce7; color: #15803d; }
.badge-correctiva { background: #eff6ff; color: #1d4ed8; }
.badge-mejora     { background: #f0fdf4; color: #16a34a; }
.badge-si         { background: #dcfce7; color: #15803d; }
.badge-no         { background: #fee2e2; color: #dc2626; }
.badge-neutral    { background: #f3f4f6; color: #6b7280; }

/* NC number */
.nc-num {
    font-weight: 700;
    color: var(--nc-navy);
    font-size: .85rem;
}

/* Empty state */
.nc-empty {
    text-align: center;
    padding: 48px 0;
    color: var(--nc-muted);
}
.nc-empty svg { margin-bottom: 12px; opacity: .3; }

/* ── Docs badge button ──────────────────────────────────────────────────────── */
.btn-docs-badge {
    background: #eff6ff;
    color: var(--nc-blue);
    border: 1px solid #bfdbfe;
    border-radius: 20px;
    padding: 2px 10px;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s, border-color .15s;
}
.btn-docs-badge:hover { background: #dbeafe; border-color: #93c5fd; }

/* ── Docs viewer mini-modal ─────────────────────────────────────────────────── */
.docs-viewer-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1100;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.docs-viewer-overlay.visible { display: flex; }
.docs-viewer-box {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 560px;
    box-shadow: 0 8px 32px rgba(0,0,0,.2);
    overflow: hidden;
}
.docs-viewer-header {
    background: var(--nc-navy);
    color: #fff;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.docs-viewer-header span { font-weight: 700; font-size: .95rem; }
.docs-viewer-close {
    background: none; border: none;
    color: rgba(255,255,255,.7);
    font-size: 1.3rem;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
    transition: color .15s;
}
.docs-viewer-close:hover { color: #fff; }
.docs-viewer-body {
    padding: 20px;
    max-height: 65vh;
    overflow-y: auto;
}
.docs-group-title {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--nc-navy);
    margin: 16px 0 8px;
    padding-bottom: 4px;
    border-bottom: 2px solid #e5e7eb;
}
.docs-group-title:first-child { margin-top: 0; }
.docs-file-list {
    list-style: none;
    padding: 0;
    margin: 0 0 4px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.docs-file-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    font-size: .82rem;
}
.docs-file-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #374151;
    font-weight: 500;
}
.docs-file-actions { display: flex; gap: 6px; flex-shrink: 0; }
.docs-file-actions a {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: .75rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 5px;
    text-decoration: none;
    transition: background .15s;
}
.btn-ver-doc  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.btn-ver-doc:hover  { background: #dbeafe; }
.btn-dl-doc   { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.btn-dl-doc:hover   { background: #dcfce7; }
.docs-viewer-empty {
    text-align: center;
    color: var(--nc-muted);
    padding: 24px 0;
    font-size: .875rem;
}

/* ── Alerts ─────────────────────────────────────────────────────────────────── */
.alert {
    padding: 11px 16px;
    border-radius: var(--nc-radius);
    font-size: .875rem;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.alert-ok   { background: #dcfce7; color: #15803d; }
.alert-err  { background: #fee2e2; color: #dc2626; }
.alert-hidden { display: none !important; }

/* ── Full-screen modal overlay ──────────────────────────────────────────────── */
.nc-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 1000;
    align-items: flex-start;
    justify-content: center;
    overflow-y: auto;
    padding: 32px 16px;
}
.nc-modal-overlay.visible { display: flex; }

.nc-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 900px;
    box-shadow: 0 12px 48px rgba(0,0,0,.22);
    overflow: hidden;
    margin: auto;
}

/* Modal header */
.nc-modal-header {
    background: var(--nc-navy);
    color: #fff;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nc-modal-header h2 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
}
.nc-modal-close {
    background: none;
    border: none;
    color: rgba(255,255,255,.7);
    cursor: pointer;
    font-size: 1.4rem;
    line-height: 1;
    padding: 0 4px;
    transition: color .15s;
}
.nc-modal-close:hover { color: #fff; }

/* Modal body */
.nc-modal-body {
    padding: 24px 24px;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
}

/* Section headers inside modal */
.nc-section {
    margin-bottom: 24px;
}
.nc-section-title {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--nc-navy);
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 6px;
    margin-bottom: 14px;
}

/* Grid layout for form fields */
.nc-grid {
    display: grid;
    gap: 14px;
}
.nc-grid-2 { grid-template-columns: 1fr 1fr; }
.nc-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
@media (max-width: 600px) {
    .nc-grid-2, .nc-grid-3 { grid-template-columns: 1fr; }
}

/* Form fields */
.nc-field { display: flex; flex-direction: column; gap: 4px; }
.nc-field label {
    font-size: .75rem;
    font-weight: 600;
    color: #374151;
}
.nc-field label .req { color: #dc2626; margin-left: 2px; }
.nc-field input,
.nc-field select,
.nc-field textarea {
    padding: 8px 10px;
    border: 1px solid var(--nc-border);
    border-radius: 6px;
    font-size: .875rem;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    font-family: inherit;
    background: #fff;
}
.nc-field input:focus,
.nc-field select:focus,
.nc-field textarea:focus {
    border-color: var(--nc-navy);
    box-shadow: 0 0 0 3px rgba(13,43,94,.1);
}
.nc-field textarea { resize: vertical; min-height: 72px; }

/* Dynamic acciones rows */
.acciones-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; }
.accion-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.accion-row input[type=text] {
    flex: 1;
    padding: 7px 10px;
    border: 1px solid var(--nc-border);
    border-radius: 6px;
    font-size: .875rem;
    outline: none;
    transition: border-color .15s;
}
.accion-row input:focus { border-color: var(--nc-navy); }
.accion-row .btn-remove {
    background: none;
    border: none;
    cursor: pointer;
    color: #dc2626;
    font-size: 1rem;
    padding: 4px;
    border-radius: 5px;
    transition: background .15s;
    flex-shrink: 0;
}
.accion-row .btn-remove:hover { background: #fee2e2; }
.btn-add-row {
    background: none;
    border: 1px dashed #d1d5db;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: .8rem;
    color: var(--nc-muted);
    cursor: pointer;
    transition: border-color .15s, color .15s, background .15s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-add-row:hover { border-color: var(--nc-navy); color: var(--nc-navy); background: #f0f4ff; }

/* Doc list in modal */
.doc-list {
    list-style: none;
    padding: 0;
    margin: 8px 0 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.doc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    background: #f8fafc;
    border-radius: 6px;
    font-size: .8rem;
    border: 1px solid var(--nc-border);
}
.doc-item a { color: var(--nc-blue); text-decoration: none; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.doc-item a:hover { text-decoration: underline; }
.doc-item .btn-doc-del {
    background: none;
    border: none;
    cursor: pointer;
    color: #dc2626;
    font-size: .85rem;
    padding: 2px 4px;
    border-radius: 4px;
    flex-shrink: 0;
}
.doc-item .btn-doc-del:hover { background: #fee2e2; }

/* File input */
.file-input-wrap { margin-top: 6px; }
.file-input-wrap input[type=file] {
    font-size: .8rem;
    color: #374151;
}

/* Modal footer */
.nc-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--nc-border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f9fafb;
}
</style>
@endpush

@section('content')
<div class="container" style="padding: 28px 24px">

    {{-- ── Page header ────────────────────────────────────────────────────────── --}}
    <div class="nc-page-header">
        <div>
            <h1>📋 Control de No Conformidades</h1>
            <p>Registro y seguimiento de hallazgos, acciones inmediatas y correctivas.</p>
        </div>
        @if($esAdmin)
        <button class="btn-import" onclick="abrirModalImportar()" title="Importar NCs desde Excel">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Importar Excel
        </button>
        <button class="btn-primary" onclick="abrirModalCrear()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva NC
        </button>
        @endif
    </div>

    {{-- ── Alertas ─────────────────────────────────────────────────────────────── --}}
    <div id="alerta-ok"  class="alert alert-ok  alert-hidden">✅ <span id="alerta-ok-msg"></span></div>
    <div id="alerta-err" class="alert alert-err alert-hidden">❌ <span id="alerta-err-msg"></span></div>

    {{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
    <div class="nc-toolbar">
        <input type="search" id="filtro-buscar" placeholder="Buscar…" oninput="filtrarTabla()">
        <select id="filtro-tipo" onchange="filtrarTabla()">
            <option value="">Todos los tipos</option>
            <option value="Correctiva">Correctiva</option>
            <option value="Oportunidad de Mejora">Oportunidad de Mejora</option>
        </select>
        <select id="filtro-status" onchange="filtrarTabla()">
            <option value="">Todos los estados</option>
            <option value="Pendiente">Pendiente</option>
            <option value="Resuelta">Resuelta</option>
        </select>
    </div>

    {{-- ── Table ──────────────────────────────────────────────────────────────── --}}
    <div class="nc-table-wrap">
        <table class="nc-table" id="tabla-nc">
            <thead>
                {{-- ── Fila 1: grupos ── --}}
                <tr class="th-group">
                    <th class="th-id-group" rowspan="2">N°</th>
                    <th class="th-id-group" rowspan="2">Fecha<br>Detección</th>
                    <th class="th-id-group" rowspan="2">Origen</th>
                    <th class="th-id-group" rowspan="2">Área</th>
                    <th class="th-id-group" colspan="2">Tipo de Acción</th>
                    <th class="th-id-group" colspan="2">Detectada Por</th>
                    <th class="th-id-group" rowspan="2">Descripción del Hallazgo</th>
                    <th class="th-inm-group"  colspan="4">Medidas Inmediatas</th>
                    <th class="th-corr-group" colspan="7">Acciones Correctivas</th>
                    <th class="th-verif-group" colspan="4">Verificación de la Eficacia</th>
                    <th class="th-id-group" rowspan="2">Archivos</th>
                    @if($esAdmin)
                    <th class="th-id-group" rowspan="2">Acciones</th>
                    @endif
                </tr>
                {{-- ── Fila 2: sub-encabezados ── --}}
                <tr class="th-sub">
                    <th class="col-tipo">Correctiva</th>
                    <th class="col-tipo">Opp.<br>Mejora</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    {{-- MEDIDAS INMEDIATAS --}}
                    <th class="sub-inm col-acc">Descripción de la Acción Inmediata</th>
                    <th class="sub-inm">Resp. Nombre</th>
                    <th class="sub-inm">Resp. Cargo</th>
                    <th class="sub-inm col-status">Status Corrección<br><small>(P=Pendiente R=Resuelta)</small></th>
                    {{-- ACCIONES CORRECTIVAS --}}
                    <th class="sub-corr col-acc">Descripción de la Acción Correctiva</th>
                    <th class="sub-corr">Resp. Nombre</th>
                    <th class="sub-corr">Resp. Cargo</th>
                    <th class="sub-corr col-fecha2">F. Implementación</th>
                    <th class="sub-corr col-fecha2">F. Seguimiento</th>
                    <th class="sub-corr col-status">Status Acc.<br>Correctiva</th>
                    <th class="sub-corr col-status">Status<br>Seguimiento</th>
                    {{-- VERIFICACIÓN --}}
                    <th class="sub-verif col-eficaz">¿Acción<br>Eficaz?</th>
                    <th class="sub-verif col-fecha2">Fecha<br>Cierre</th>
                    <th class="sub-verif col-reg">Registros</th>
                    <th class="sub-verif col-obs">Observaciones</th>
                </tr>
            </thead>
            <tbody id="nc-tbody">
            @forelse($ncs as $nc)
                @php
                    $docsJson  = $nc->documentos->map(fn($d) => [
                        'tipo'   => $d->tipo,
                        'nombre' => $d->nombre_original,
                        'url'    => route('nc.doc.ver', $d->id),
                        'dl'     => route('nc.doc.ver', $d->id) . '?download=1',
                    ])->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
                    $totalDocs = $nc->documentos->count();
                @endphp
                <tr class="nc-row"
                    data-tipo="{{ $nc->tipo_nombre }}"
                    data-status="{{ $nc->status_corr_txt }}">

                    {{-- Identificación --}}
                    <td class="center col-num"><span class="nc-num">{{ $nc->num_nc }}</span></td>
                    <td class="col-fecha center">{{ $nc->fecha_deteccion?->format('d-m-Y') ?? '—' }}</td>
                    <td class="col-origen">{{ $nc->origen }}</td>
                    <td class="col-area">{{ $nc->area_nombre }}</td>
                    <td class="col-tipo center">
                        @if($nc->tipo_accion == 1)<span style="font-weight:700; color:#1d4ed8">X</span>@endif
                    </td>
                    <td class="col-tipo center">
                        @if($nc->tipo_accion == 2)<span style="font-weight:700; color:#16a34a">X</span>@endif
                    </td>
                    <td class="col-nom">{{ $nc->detectada_por }}</td>
                    <td class="col-cargo">{{ $nc->cargo }}</td>
                    <td class="col-hall">{{ $nc->descripcion }}</td>

                    {{-- Medidas inmediatas --}}
                    <td class="td-inm col-acc">
                        @if($nc->accionesInmediatas->isNotEmpty())
                            <ul class="acc-list">
                                @foreach($nc->accionesInmediatas as $i => $acc)
                                <li>
                                    <span class="acc-num">{{ $i+1 }}.-</span>
                                    <span class="acc-txt">{{ $acc->descripcion }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="cell-dash">—</span>
                        @endif
                    </td>
                    <td class="td-inm col-nom">{{ $nc->resp_inmediata_nombre ?: '—' }}</td>
                    <td class="td-inm col-cargo">{{ $nc->resp_inmediata_cargo ?: '—' }}</td>
                    <td class="td-inm col-status center">
                        <span class="badge {{ $nc->status_correccion == 1 ? 'badge-resuelta' : 'badge-pendiente' }}">
                            {{ $nc->status_corr_txt }}
                        </span>
                    </td>

                    {{-- Acciones correctivas --}}
                    <td class="td-corr col-acc">
                        @if($nc->accionesCorrectivas->isNotEmpty())
                            <ul class="acc-list">
                                @foreach($nc->accionesCorrectivas as $i => $acc)
                                <li>
                                    <span class="acc-num">{{ $i+1 }}.-</span>
                                    <span class="acc-txt">{{ $acc->descripcion }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="cell-dash">—</span>
                        @endif
                    </td>
                    <td class="td-corr col-nom">{{ $nc->resp_correctiva_nombre ?: '—' }}</td>
                    <td class="td-corr col-cargo">{{ $nc->resp_correctiva_cargo ?: '—' }}</td>
                    <td class="td-corr col-fecha2 center">{{ $nc->fecha_implem_acc_corr?->format('d-m-Y') ?? '—' }}</td>
                    <td class="td-corr col-fecha2 center">{{ $nc->fecha_seguim_acc_corr?->format('d-m-Y') ?? '—' }}</td>
                    <td class="td-corr col-status center">
                        <span class="badge {{ $nc->status_acc_corr == 1 ? 'badge-resuelta' : 'badge-pendiente' }}">
                            {{ $nc->status_acc_txt }}
                        </span>
                    </td>
                    <td class="td-corr col-status center">
                        <span class="badge {{ $nc->status_seguim_acc_corr == 1 ? 'badge-resuelta' : 'badge-pendiente' }}">
                            {{ $nc->status_seguim_txt }}
                        </span>
                    </td>

                    {{-- Verificación --}}
                    <td class="td-verif col-eficaz center">
                        @if($nc->accion_eficaz == 1)
                            <span class="badge badge-si">Sí</span>
                        @elseif($nc->accion_eficaz == 2)
                            <span class="badge badge-no">No</span>
                        @else
                            <span class="cell-dash">—</span>
                        @endif
                    </td>
                    <td class="td-verif col-fecha2 center">{{ $nc->fecha_cierre?->format('d-m-Y') ?? '—' }}</td>
                    <td class="td-verif col-reg">{{ $nc->registros ?: '—' }}</td>
                    <td class="td-verif col-obs">{{ $nc->observaciones ?: '—' }}</td>

                    {{-- Archivos --}}
                    <td class="col-docs center">
                        @if($totalDocs > 0)
                        <button class="btn-docs-badge"
                                onclick="verDocs(this, {{ $nc->num_nc }})"
                                data-docs="{{ htmlspecialchars($docsJson, ENT_QUOTES, 'UTF-8') }}">
                            📎 {{ $totalDocs }}
                        </button>
                        @else
                        <span class="cell-dash">—</span>
                        @endif
                    </td>

                    {{-- Acciones admin --}}
                    @if($esAdmin)
                    <td class="col-act center">
                        <button class="btn-icon" title="Editar" onclick="abrirModalEditar({{ $nc->id }})">✏️</button>
                        <button class="btn-icon danger" title="Eliminar" onclick="eliminarNC({{ $nc->id }}, {{ $nc->num_nc }})">🗑️</button>
                    </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $esAdmin ? 26 : 25 }}" class="nc-empty">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        <p>No hay No Conformidades registradas.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{--  MODAL CREAR / EDITAR                                                       --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@if($esAdmin)
<div class="nc-modal-overlay" id="nc-modal" onclick="onOverlayClick(event)">
<div class="nc-modal">

    {{-- Header --}}
    <div class="nc-modal-header">
        <h2 id="modal-titulo">Nueva No Conformidad</h2>
        <button class="nc-modal-close" onclick="cerrarModal()" title="Cerrar">&times;</button>
    </div>

    {{-- Body --}}
    <div class="nc-modal-body">
        <input type="hidden" id="nc-edit-id" value="">

        {{-- ── 1. Datos del hallazgo ──────────────────────────────────────────── --}}
        <div class="nc-section">
            <div class="nc-section-title">1. Datos del hallazgo</div>
            <div class="nc-grid nc-grid-3">
                <div class="nc-field">
                    <label>Fecha de detección <span class="req">*</span></label>
                    <input type="date" id="f-fecha_deteccion">
                </div>
                <div class="nc-field">
                    <label>Origen <span class="req">*</span></label>
                    <input type="text" id="f-origen" placeholder="Ej: Auditoría interna" maxlength="100">
                </div>
                <div class="nc-field">
                    <label>Área</label>
                    <select id="f-id_area">
                        <option value="">— Sin área —</option>
                        @foreach($areas as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="nc-grid nc-grid-3" style="margin-top:14px">
                <div class="nc-field">
                    <label>Tipo de acción <span class="req">*</span></label>
                    <select id="f-tipo_accion">
                        <option value="1">Correctiva</option>
                        <option value="2">Oportunidad de Mejora</option>
                    </select>
                </div>
                <div class="nc-field">
                    <label>Detectada por <span class="req">*</span></label>
                    <input type="text" id="f-detectada_por" placeholder="Nombre completo" maxlength="150">
                </div>
                <div class="nc-field">
                    <label>Cargo <span class="req">*</span></label>
                    <input type="text" id="f-cargo" placeholder="Cargo quien detectó" maxlength="100">
                </div>
            </div>
            <div class="nc-grid" style="margin-top:14px">
                <div class="nc-field">
                    <label>Descripción del hallazgo <span class="req">*</span></label>
                    <textarea id="f-descripcion" rows="3" placeholder="Describe el hallazgo o la no conformidad detectada…"></textarea>
                </div>
            </div>
        </div>

        {{-- ── 2. Medidas inmediatas ───────────────────────────────────────────── --}}
        <div class="nc-section">
            <div class="nc-section-title">2. Medidas inmediatas (corrección)</div>
            <div class="nc-grid nc-grid-2">
                <div class="nc-field">
                    <label>Responsable — Nombre</label>
                    <input type="text" id="f-resp_inmediata_nombre" placeholder="Nombre del responsable" maxlength="150">
                </div>
                <div class="nc-field">
                    <label>Responsable — Cargo</label>
                    <input type="text" id="f-resp_inmediata_cargo" placeholder="Cargo" maxlength="100">
                </div>
            </div>
            <div class="nc-field" style="margin-top:12px; max-width:220px">
                <label>Estado de corrección</label>
                <select id="f-status_correccion">
                    <option value="0">Pendiente</option>
                    <option value="1">Resuelta</option>
                </select>
            </div>

            <div style="margin-top:14px">
                <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:8px">
                    Acciones inmediatas
                </label>
                <div class="acciones-list" id="lista-acciones-inmediatas"></div>
                <button type="button" class="btn-add-row" onclick="agregarAccion('inmediatas')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Agregar acción
                </button>
            </div>

            <div class="file-input-wrap" style="margin-top:14px">
                <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:6px">
                    Evidencias (archivos) — Medidas inmediatas
                </label>
                <ul class="doc-list" id="docs-inmediatas-existentes"></ul>
                <input type="file" id="f-docs_inmediatas" name="docs_inmediatas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
            </div>
        </div>

        {{-- ── 3. Acciones correctivas ─────────────────────────────────────────── --}}
        <div class="nc-section">
            <div class="nc-section-title">3. Acciones correctivas</div>
            <div class="nc-grid nc-grid-2">
                <div class="nc-field">
                    <label>Responsable — Nombre</label>
                    <input type="text" id="f-resp_correctiva_nombre" placeholder="Nombre del responsable" maxlength="150">
                </div>
                <div class="nc-field">
                    <label>Responsable — Cargo</label>
                    <input type="text" id="f-resp_correctiva_cargo" placeholder="Cargo" maxlength="100">
                </div>
            </div>
            <div class="nc-grid nc-grid-2" style="margin-top:12px">
                <div class="nc-field">
                    <label>Fecha implementación</label>
                    <input type="date" id="f-fecha_implem_acc_corr">
                </div>
                <div class="nc-field">
                    <label>Fecha seguimiento</label>
                    <input type="date" id="f-fecha_seguim_acc_corr">
                </div>
            </div>
            <div class="nc-grid nc-grid-3" style="margin-top:12px">
                <div class="nc-field">
                    <label>Estado acc. correctiva</label>
                    <select id="f-status_acc_corr">
                        <option value="0">Pendiente</option>
                        <option value="1">Resuelta</option>
                    </select>
                </div>
                <div class="nc-field">
                    <label>Estado seguimiento</label>
                    <select id="f-status_seguim_acc_corr">
                        <option value="0">Pendiente</option>
                        <option value="1">Resuelta</option>
                    </select>
                </div>
                <div class="nc-field">
                    <label>Acción eficaz</label>
                    <select id="f-accion_eficaz">
                        <option value="0">—</option>
                        <option value="1">Sí</option>
                        <option value="2">No</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:14px">
                <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:8px">
                    Acciones correctivas
                </label>
                <div class="acciones-list" id="lista-acciones-correctivas"></div>
                <button type="button" class="btn-add-row" onclick="agregarAccion('correctivas')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Agregar acción
                </button>
            </div>

            <div class="file-input-wrap" style="margin-top:14px">
                <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:6px">
                    Evidencias (archivos) — Acciones correctivas
                </label>
                <ul class="doc-list" id="docs-correctivas-existentes"></ul>
                <input type="file" id="f-docs_correctivas" name="docs_correctivas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
            </div>
        </div>

        {{-- ── 4. Verificación de eficacia ─────────────────────────────────────── --}}
        <div class="nc-section">
            <div class="nc-section-title">4. Cierre y observaciones</div>
            <div class="nc-grid nc-grid-2">
                <div class="nc-field">
                    <label>Fecha de cierre</label>
                    <input type="date" id="f-fecha_cierre">
                </div>
                <div class="nc-field">
                    <label>Registros / Evidencias textuales</label>
                    <input type="text" id="f-registros" placeholder="Opcional" maxlength="255">
                </div>
            </div>
            <div class="nc-field" style="margin-top:12px">
                <label>Observaciones</label>
                <textarea id="f-observaciones" rows="2" placeholder="Observaciones adicionales…"></textarea>
            </div>
        </div>

    </div>{{-- /.nc-modal-body --}}

    {{-- Footer --}}
    <div class="nc-modal-footer">
        <button class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
        <button class="btn-primary" id="btn-guardar-nc" onclick="guardarNC()">
            <span id="btn-guardar-txt">Guardar</span>
        </button>
    </div>

</div>{{-- /.nc-modal --}}
</div>{{-- /.nc-modal-overlay --}}
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{--  MODAL IMPORTAR EXCEL                                                        --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@if($esAdmin)
<div class="docs-viewer-overlay" id="modal-importar" onclick="if(event.target===this)cerrarImportar()">
    <div class="docs-viewer-box" style="max-width:520px">
        <div class="docs-viewer-header">
            <span>📥 Importar No Conformidades desde Excel</span>
            <button class="docs-viewer-close" onclick="cerrarImportar()">&times;</button>
        </div>
        <div class="docs-viewer-body" style="padding:24px">

            <p style="font-size:.875rem; color:#374151; margin:0 0 16px">
                Sube el archivo <strong>Matriz_seguimiento_NC</strong> en formato <code>.xlsx</code>.<br>
                Las NCs que ya existan (por N° NC) serán omitidas automáticamente.
            </p>

            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 14px; font-size:.8rem; color:#92400e; margin-bottom:18px">
                <strong>⚠️ Estructura esperada:</strong> encabezados en filas 6–8, datos desde fila 9.
                Las columnas deben seguir el formato de la Matriz de Seguimiento NC.
            </div>

            <div class="nc-field" style="margin-bottom:18px">
                <label style="font-size:.8rem; font-weight:600; color:#374151; display:block; margin-bottom:6px">
                    Archivo Excel <span style="color:#dc2626">*</span>
                </label>
                <input type="file" id="import-file" accept=".xlsx,.xls"
                       style="font-size:.875rem; width:100%">
            </div>

            <!-- Resultado de la importación -->
            <div id="import-resultado" style="display:none"></div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px">
                <button class="btn-secondary" onclick="cerrarImportar()">Cancelar</button>
                <button class="btn-primary" id="btn-importar" onclick="ejecutarImport()" style="background:#15803d">
                    <span id="btn-importar-txt">Importar</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{--  MINI-VISOR DE DOCUMENTOS                                                   --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="docs-viewer-overlay" id="docs-viewer" onclick="if(event.target===this)cerrarDocsViewer()">
    <div class="docs-viewer-box">
        <div class="docs-viewer-header">
            <span id="docs-viewer-titulo">Archivos adjuntos — NC</span>
            <button class="docs-viewer-close" onclick="cerrarDocsViewer()" title="Cerrar">&times;</button>
        </div>
        <div class="docs-viewer-body" id="docs-viewer-body">
            {{-- contenido dinámico --}}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const NC_BASE     = "{{ url('/no-conformidades') }}";
const NC_DOC_BASE = "{{ url('/nc/doc') }}";
const ES_ADMIN    = {{ $esAdmin ? 'true' : 'false' }};

/* ════════════════════════════════════════════════════════════════════════════
   ALERTAS
════════════════════════════════════════════════════════════════════════════ */
let alertTimer = null;
function mostrarOk(msg) {
    clearTimeout(alertTimer);
    document.getElementById('alerta-err').classList.add('alert-hidden');
    const el = document.getElementById('alerta-ok');
    document.getElementById('alerta-ok-msg').textContent = msg;
    el.classList.remove('alert-hidden');
    alertTimer = setTimeout(() => el.classList.add('alert-hidden'), 5000);
}
function mostrarErr(msg) {
    clearTimeout(alertTimer);
    document.getElementById('alerta-ok').classList.add('alert-hidden');
    const el = document.getElementById('alerta-err');
    document.getElementById('alerta-err-msg').textContent = msg;
    el.classList.remove('alert-hidden');
}

/* ════════════════════════════════════════════════════════════════════════════
   FILTROS
════════════════════════════════════════════════════════════════════════════ */
function filtrarTabla() {
    const q      = document.getElementById('filtro-buscar').value.toLowerCase();
    const tipo   = document.getElementById('filtro-tipo').value;
    const status = document.getElementById('filtro-status').value;

    document.querySelectorAll('#nc-tbody .nc-row').forEach(tr => {
        const texto   = tr.textContent.toLowerCase();
        const trTipo  = tr.dataset.tipo   || '';
        const trSt    = tr.dataset.status || '';

        const matchQ  = !q      || texto.includes(q);
        const matchT  = !tipo   || trTipo === tipo;
        const matchS  = !status || trSt   === status;

        tr.style.display = (matchQ && matchT && matchS) ? '' : 'none';
    });
}

/* ════════════════════════════════════════════════════════════════════════════
   MODAL HELPERS
════════════════════════════════════════════════════════════════════════════ */
function cerrarModal() {
    document.getElementById('nc-modal').classList.remove('visible');
}
function onOverlayClick(e) {
    if (e.target === document.getElementById('nc-modal')) cerrarModal();
}
function resetForm() {
    // Limpiar campos de texto y fechas
    [
        'f-fecha_deteccion','f-origen','f-id_area','f-tipo_accion','f-detectada_por',
        'f-cargo','f-descripcion','f-resp_inmediata_nombre','f-resp_inmediata_cargo',
        'f-status_correccion','f-resp_correctiva_nombre','f-resp_correctiva_cargo',
        'f-fecha_implem_acc_corr','f-fecha_seguim_acc_corr','f-status_acc_corr',
        'f-status_seguim_acc_corr','f-accion_eficaz','f-fecha_cierre',
        'f-registros','f-observaciones',
    ].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });

    document.getElementById('nc-edit-id').value = '';
    document.getElementById('lista-acciones-inmediatas').innerHTML  = '';
    document.getElementById('lista-acciones-correctivas').innerHTML = '';
    document.getElementById('docs-inmediatas-existentes').innerHTML  = '';
    document.getElementById('docs-correctivas-existentes').innerHTML = '';
    document.getElementById('f-docs_inmediatas').value  = '';
    document.getElementById('f-docs_correctivas').value = '';
}

function setVal(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = val ?? '';
}

/* ════════════════════════════════════════════════════════════════════════════
   ACCIONES DINÁMICAS
════════════════════════════════════════════════════════════════════════════ */
function agregarAccion(tipo, valor = '') {
    const listId = tipo === 'inmediatas'
        ? 'lista-acciones-inmediatas'
        : 'lista-acciones-correctivas';

    const list = document.getElementById(listId);
    const row  = document.createElement('div');
    row.className = 'accion-row';

    const inputName = tipo === 'inmediatas'
        ? 'acciones_inmediatas[]'
        : 'acciones_correctivas[]';

    row.innerHTML = `
        <input type="text" name="${inputName}" value="${escHtml(valor)}"
               placeholder="Descripción de la acción…" maxlength="500">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Quitar">✕</button>`;
    list.appendChild(row);
    row.querySelector('input').focus();
}

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g,'&amp;')
        .replace(/"/g,'&quot;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;');
}

/* ════════════════════════════════════════════════════════════════════════════
   ABRIR MODAL — CREAR
════════════════════════════════════════════════════════════════════════════ */
function abrirModalCrear() {
    resetForm();
    document.getElementById('modal-titulo').textContent = 'Nueva No Conformidad';
    document.getElementById('btn-guardar-txt').textContent = 'Crear NC';
    agregarAccion('inmediatas');
    agregarAccion('correctivas');
    document.getElementById('nc-modal').classList.add('visible');
    setTimeout(() => document.getElementById('f-fecha_deteccion').focus(), 80);
}

/* ════════════════════════════════════════════════════════════════════════════
   ABRIR MODAL — EDITAR
════════════════════════════════════════════════════════════════════════════ */
async function abrirModalEditar(id) {
    resetForm();
    document.getElementById('modal-titulo').textContent = 'Editando No Conformidad…';
    document.getElementById('btn-guardar-txt').textContent = 'Guardar cambios';
    document.getElementById('nc-modal').classList.add('visible');

    try {
        const res  = await fetch(`${NC_BASE}/${id}/datos`);
        const data = await res.json();
        const nc   = data.nc;

        document.getElementById('nc-edit-id').value = nc.id;
        document.getElementById('modal-titulo').textContent = `Editando NC N° ${nc.num_nc}`;

        setVal('f-fecha_deteccion',          nc.fecha_deteccion);
        setVal('f-origen',                   nc.origen);
        setVal('f-id_area',                  nc.id_area ?? '');
        setVal('f-tipo_accion',              nc.tipo_accion);
        setVal('f-detectada_por',            nc.detectada_por);
        setVal('f-cargo',                    nc.cargo);
        setVal('f-descripcion',              nc.descripcion);
        setVal('f-resp_inmediata_nombre',    nc.resp_inmediata_nombre);
        setVal('f-resp_inmediata_cargo',     nc.resp_inmediata_cargo);
        setVal('f-status_correccion',        nc.status_correccion);
        setVal('f-resp_correctiva_nombre',   nc.resp_correctiva_nombre);
        setVal('f-resp_correctiva_cargo',    nc.resp_correctiva_cargo);
        setVal('f-fecha_implem_acc_corr',    nc.fecha_implem_acc_corr);
        setVal('f-fecha_seguim_acc_corr',    nc.fecha_seguim_acc_corr);
        setVal('f-status_acc_corr',          nc.status_acc_corr);
        setVal('f-status_seguim_acc_corr',   nc.status_seguim_acc_corr);
        setVal('f-accion_eficaz',            nc.accion_eficaz);
        setVal('f-fecha_cierre',             nc.fecha_cierre);
        setVal('f-registros',                nc.registros);
        setVal('f-observaciones',            nc.observaciones);

        // Acciones inmediatas
        if (data.acciones_inmediatas?.length) {
            data.acciones_inmediatas.forEach(a => agregarAccion('inmediatas', a.descripcion));
        } else {
            agregarAccion('inmediatas');
        }

        // Acciones correctivas
        if (data.acciones_correctivas?.length) {
            data.acciones_correctivas.forEach(a => agregarAccion('correctivas', a.descripcion));
        } else {
            agregarAccion('correctivas');
        }

        // Documentos existentes
        const docInm  = document.getElementById('docs-inmediatas-existentes');
        const docCorr = document.getElementById('docs-correctivas-existentes');

        data.documentos.forEach(d => {
            const li = document.createElement('li');
            li.className = 'doc-item';
            li.id = `doc-item-${d.id}`;
            li.innerHTML = `
                📎 <a href="${d.url}" target="_blank">${escHtml(d.nombre_original)}</a>
                <button class="btn-doc-del" onclick="eliminarDocExistente(${d.id})" title="Eliminar archivo">✕</button>`;
            (d.tipo == 1 ? docInm : docCorr).appendChild(li);
        });

    } catch(e) {
        cerrarModal();
        mostrarErr('No se pudo cargar la NC. Intenta de nuevo.');
    }
}

/* ════════════════════════════════════════════════════════════════════════════
   ELIMINAR DOC EXISTENTE (desde modal edición)
════════════════════════════════════════════════════════════════════════════ */
async function eliminarDocExistente(docId) {
    if (!confirm('¿Eliminar este archivo de evidencia?')) return;
    try {
        const res = await window.sgcFetch(`${NC_DOC_BASE}/${docId}`, { method: 'DELETE' });
        if (!res.ok) { mostrarErr('No se pudo eliminar el archivo.'); return; }
        document.getElementById(`doc-item-${docId}`)?.remove();
    } catch(e) {
        mostrarErr('Error de conexión.');
    }
}

/* ════════════════════════════════════════════════════════════════════════════
   GUARDAR (CREAR / ACTUALIZAR)
════════════════════════════════════════════════════════════════════════════ */
async function guardarNC() {
    const editId = document.getElementById('nc-edit-id').value;
    const esEdit = !!editId;

    // Validación básica
    const req = ['f-fecha_deteccion','f-origen','f-detectada_por','f-cargo','f-descripcion'];
    let hayError = false;
    req.forEach(id => {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            el.style.borderColor = '#dc2626';
            hayError = true;
            setTimeout(() => el.style.borderColor = '', 2000);
        }
    });
    if (hayError) { mostrarErr('Completa los campos obligatorios marcados en rojo.'); return; }

    const btn = document.getElementById('btn-guardar-nc');
    btn.disabled = true;
    document.getElementById('btn-guardar-txt').textContent = 'Guardando…';

    try {
        // Construir FormData (necesario para archivos)
        const fd = new FormData();
        fd.append('_token', window.CSRF_TOKEN);
        if (esEdit) fd.append('_method', 'PUT');

        const campos = [
            'fecha_deteccion','origen','id_area','tipo_accion','detectada_por','cargo',
            'descripcion','resp_inmediata_nombre','resp_inmediata_cargo','status_correccion',
            'resp_correctiva_nombre','resp_correctiva_cargo','fecha_implem_acc_corr',
            'fecha_seguim_acc_corr','status_acc_corr','status_seguim_acc_corr',
            'accion_eficaz','fecha_cierre','registros','observaciones',
        ];
        campos.forEach(c => {
            const v = document.getElementById(`f-${c}`)?.value ?? '';
            fd.append(c, v);
        });

        // Acciones
        document.querySelectorAll('#lista-acciones-inmediatas input[name="acciones_inmediatas[]"]')
            .forEach(inp => fd.append('acciones_inmediatas[]', inp.value));
        document.querySelectorAll('#lista-acciones-correctivas input[name="acciones_correctivas[]"]')
            .forEach(inp => fd.append('acciones_correctivas[]', inp.value));

        // Archivos nuevos
        const filesInm  = document.getElementById('f-docs_inmediatas').files;
        const filesCorr = document.getElementById('f-docs_correctivas').files;
        for (const f of filesInm)  fd.append('docs_inmediatas[]', f);
        for (const f of filesCorr) fd.append('docs_correctivas[]', f);

        const url    = esEdit ? `${NC_BASE}/${editId}` : NC_BASE;
        const method = 'POST'; // _method override handles PUT

        const res  = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN },
            body: fd,
        });
        const data = await res.json();

        if (!res.ok) {
            const msgs = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.error || data.message || 'Error al guardar.');
            mostrarErr(msgs);
            return;
        }

        cerrarModal();
        mostrarOk(data.mensaje || (esEdit ? 'NC actualizada.' : 'NC creada.'));

        // Recargar página para reflejar cambios en tabla
        setTimeout(() => location.reload(), 900);

    } catch(e) {
        mostrarErr('Error de conexión. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        document.getElementById('btn-guardar-txt').textContent = esEdit ? 'Guardar cambios' : 'Crear NC';
    }
}

/* ════════════════════════════════════════════════════════════════════════════
   ELIMINAR NC
════════════════════════════════════════════════════════════════════════════ */
async function eliminarNC(id, num) {
    if (!confirm(`¿Eliminar la No Conformidad N° ${num}?\n\nSe eliminarán también todas sus acciones y documentos asociados. Esta acción no se puede deshacer.`)) return;

    try {
        const res  = await window.sgcFetch(`${NC_BASE}/${id}`, { method: 'DELETE' });
        const data = await res.json();

        if (!res.ok) { mostrarErr(data.error || 'No se pudo eliminar.'); return; }

        mostrarOk(data.mensaje || `NC N° ${num} eliminada.`);
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarErr('Error de conexión.');
    }
}

/* ════════════════════════════════════════════════════════════════════════════
   VISOR DE DOCUMENTOS
════════════════════════════════════════════════════════════════════════════ */
function verDocs(btn, numNc) {
    let docs;
    try {
        docs = JSON.parse(btn.getAttribute('data-docs'));
    } catch(e) {
        mostrarErr('No se pudo leer la lista de archivos.');
        return;
    }

    document.getElementById('docs-viewer-titulo').textContent = `Archivos adjuntos — NC N° ${numNc}`;

    const inmediatas  = docs.filter(d => d.tipo == 1);
    const correctivas = docs.filter(d => d.tipo == 2);

    let html = '';

    if (inmediatas.length) {
        html += `<p class="docs-group-title">📁 Evidencias — Medidas inmediatas</p>
                 <ul class="docs-file-list">`;
        inmediatas.forEach(d => {
            html += `
            <li class="docs-file-item">
                <span class="docs-file-name" title="${escHtml(d.nombre)}">📄 ${escHtml(d.nombre)}</span>
                <div class="docs-file-actions">
                    <a href="${d.url}" target="_blank" class="btn-ver-doc">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Ver
                    </a>
                    <a href="${d.dl}" class="btn-dl-doc">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Descargar
                    </a>
                </div>
            </li>`;
        });
        html += `</ul>`;
    }

    if (correctivas.length) {
        html += `<p class="docs-group-title">📁 Evidencias — Acciones correctivas</p>
                 <ul class="docs-file-list">`;
        correctivas.forEach(d => {
            html += `
            <li class="docs-file-item">
                <span class="docs-file-name" title="${escHtml(d.nombre)}">📄 ${escHtml(d.nombre)}</span>
                <div class="docs-file-actions">
                    <a href="${d.url}" target="_blank" class="btn-ver-doc">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Ver
                    </a>
                    <a href="${d.dl}" class="btn-dl-doc">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Descargar
                    </a>
                </div>
            </li>`;
        });
        html += `</ul>`;
    }

    if (!html) {
        html = '<p class="docs-viewer-empty">No hay archivos adjuntos para esta NC.</p>';
    }

    document.getElementById('docs-viewer-body').innerHTML = html;
    document.getElementById('docs-viewer').classList.add('visible');
}

function cerrarDocsViewer() {
    document.getElementById('docs-viewer').classList.remove('visible');
}

/* ════════════════════════════════════════════════════════════════════════════
   IMPORTAR EXCEL
════════════════════════════════════════════════════════════════════════════ */
function abrirModalImportar() {
    document.getElementById('import-file').value = '';
    document.getElementById('import-resultado').style.display = 'none';
    document.getElementById('import-resultado').innerHTML = '';
    document.getElementById('btn-importar-txt').textContent = 'Importar';
    document.getElementById('btn-importar').disabled = false;
    document.getElementById('modal-importar').classList.add('visible');
}
function cerrarImportar() {
    document.getElementById('modal-importar').classList.remove('visible');
}

async function ejecutarImport() {
    const fileInput = document.getElementById('import-file');
    if (!fileInput.files.length) {
        fileInput.focus();
        mostrarResultadoImport('error', 'Selecciona un archivo Excel primero.');
        return;
    }

    const btn = document.getElementById('btn-importar');
    btn.disabled = true;
    document.getElementById('btn-importar-txt').textContent = 'Procesando…';
    document.getElementById('import-resultado').style.display = 'none';

    const fd = new FormData();
    fd.append('archivo_excel', fileInput.files[0]);
    fd.append('_token', window.CSRF_TOKEN);

    try {
        const res  = await fetch("{{ route('nc.importar') }}", { method: 'POST', body: fd });
        const data = await res.json();

        if (!res.ok) {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.error || 'Error al importar.');
            mostrarResultadoImport('error', msg);
            return;
        }

        // Construir resumen visual
        let html = `<div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;padding:14px 16px;font-size:.85rem;color:#15803d;margin-bottom:12px">
            ✅ <strong>${data.mensaje}</strong>
        </div>`;

        if (data.importadas?.length) {
            html += `<p style="font-size:.78rem;color:#374151;margin:0 0 4px"><strong>Importadas (${data.importadas.length}):</strong> NC ${data.importadas.map(n=>'#'+n).join(', ')}</p>`;
        }
        if (data.omitidas?.length) {
            html += `<p style="font-size:.78rem;color:#92400e;margin:4px 0"><strong>Omitidas — ya existían (${data.omitidas.length}):</strong> NC ${data.omitidas.map(n=>'#'+n).join(', ')}</p>`;
        }
        if (data.errores?.length) {
            html += `<p style="font-size:.78rem;color:#dc2626;margin:4px 0"><strong>Errores:</strong><br>${data.errores.map(e=>escHtml(e)).join('<br>')}</p>`;
        }

        if (data.importadas?.length) {
            html += `<button class="btn-primary" style="margin-top:12px;width:100%;justify-content:center" onclick="location.reload()">
                Ver No Conformidades importadas →
            </button>`;
        }

        document.getElementById('import-resultado').innerHTML = html;
        document.getElementById('import-resultado').style.display = 'block';

    } catch(e) {
        mostrarResultadoImport('error', 'Error de conexión. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        document.getElementById('btn-importar-txt').textContent = 'Importar';
    }
}

function mostrarResultadoImport(tipo, msg) {
    const el = document.getElementById('import-resultado');
    const color = tipo === 'error' ? '#fee2e2' : '#dcfce7';
    const borde = tipo === 'error' ? '#fecaca' : '#bbf7d0';
    const texto = tipo === 'error' ? '#dc2626' : '#15803d';
    el.innerHTML = `<div style="background:${color};border:1px solid ${borde};border-radius:8px;padding:12px 14px;font-size:.85rem;color:${texto}">${escHtml(msg)}</div>`;
    el.style.display = 'block';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        cerrarDocsViewer();
        cerrarModal();
    }
});
</script>
@endpush
