<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    Dashboard Admin
                </h2>
                <p class="text-xs text-slate-500 mt-1">Kelola dokumen dan konfigurasi respon bot.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $systemReady ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}" role="status">
                <span class="w-2 h-2 rounded-full {{ $systemReady ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                {{ $systemReady ? 'Sistem Siap' : 'Perlu Pemeriksaan' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Notifikasi -->
        @if(session('success'))
            <div class="flex items-center p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm" role="status" aria-live="polite">
                <svg class="w-5 h-5 mr-3 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="flex items-center p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm" role="alert" aria-live="assertive">
                <svg class="w-5 h-5 mr-3 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-medium">{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Cards Ringkasan / Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Database</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalKnowledge ?? $knowledges->total() }} Dokumen</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Nama Asisten Bot</p>
                    <p class="text-lg font-bold text-slate-800 mt-1 truncate max-w-[180px]">{{ $settings['bot_name'] ?? 'Asisten Virtual' }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Penyimpanan Teks</p>
                    <p class="text-lg font-bold {{ $databaseConnected ? 'text-slate-800' : 'text-rose-700' }} mt-1">
                        {{ $databaseConnected ? 'Database Terhubung' : 'Database Bermasalah' }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
            </div>
        </div>

        <!-- Main Layout (Grid 2 Kolom) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Kolom Kiri: Form Input Knowledge (7/12) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 text-sm">1. Tambah Informasi</h3>
                        <span class="text-xs text-slate-400">knowledge_bases</span>
                    </div>
                    
                    <form x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); return; } submitting = true" action="{{ route('admin.knowledge.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="knowledge-cluster" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Klaster Layanan *</label>
                                <select id="knowledge-cluster" name="cluster" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2 bg-white">
                                    <option value="" disabled {{ old('cluster') ? '' : 'selected' }}>-- Pilih Klaster --</option>
                                    @foreach($availableClusters as $cluster)
                                        <option value="{{ $cluster }}" {{ old('cluster') == $cluster ? 'selected' : '' }}>{{ $cluster }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="knowledge-topic" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Topik / Judul Informasi *</label>
                                <input id="knowledge-topic" type="text" name="topic" value="{{ old('topic') }}" required placeholder="Contoh: Prosedur Pengajuan KTP" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2">
                            </div>
                        </div>

                        <div>
                            <label for="knowledge-keywords" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kata Kunci / Keywords Pencarian</label>
                            <input id="knowledge-keywords" type="text" name="keywords" value="{{ old('keywords') }}" placeholder="Contoh: ktp, e-ktp, disdukcapil, identitas, perekaman" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2">
                            <p class="text-[11px] text-slate-400 mt-0.5">Pisahkan kata kunci dengan tanda koma.</p>
                        </div>

                        <div>
                            <label for="knowledge-source-url" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">URL Sumber Referensi (Opsional)</label>
                            <input id="knowledge-source-url" type="url" name="source_url" value="{{ old('source_url') }}" placeholder="Contoh: https://www.bandung.go.id/" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2">
                            
                            {{-- Opsi: Hanya Referensi vs Scrape Otomatis --}}
                            <div class="mt-2 flex items-start gap-2.5 p-2.5 bg-slate-50 hover:bg-slate-100/80 transition-all rounded-xl border border-slate-200">
                                <input id="auto-scrape-url" type="checkbox" name="auto_scrape_url" value="1" {{ old('auto_scrape_url') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                <label for="auto-scrape-url" class="text-xs text-slate-700 cursor-pointer select-none">
                                    <span class="font-semibold text-indigo-900">Aktifkan Scrape Otomatis dari URL</span>
                                    <span class="block text-[11px] text-slate-500 font-normal mt-0.5 leading-relaxed">
                                        Centang jika Anda ingin sistem otomatis mengunduh & mengekstrak isi teks/gambar/PDF dari URL.<br>
                                        <span class="text-amber-700 font-medium">💡 Jika tidak dicentang:</span> URL hanya akan disimpan sebagai <em>tautan rujukan/sitasi</em> tanpa menimpa isi teks yang sudah Anda ketik di kolom "Isi Konten Informasi".
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="knowledge-pdf" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Upload File Dokumen atau Gambar (PDF / PNG / JPG / WEBP)</label>
                            <input id="knowledge-pdf" type="file" name="pdf_file" accept=".pdf,.png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border border-slate-200 rounded-xl p-1">
                            <p class="text-[11px] text-slate-400 mt-0.5">Teks dokumen PDF atau gambar brosur/flyer akan diekstrak secara otomatis melalui OCR.</p>
                        </div>

                        <div>
                            <label for="knowledge-content" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Isi Konten Informasi</label>
                            <textarea id="knowledge-content" name="content" rows="4" placeholder="Ketik deskripsi, syarat, prosedur, atau jawaban manual di sini (atau biarkan kosong jika otomatis diekstrak dari URL / File di atas)..." class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2">{{ old('content') }}</textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" :disabled="submitting" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60 text-white font-medium text-sm rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                <span x-text="submitting ? 'Menyimpan data...' : 'Simpan ke Knowledge Base'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Data Knowledge -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Daftar Database</h3>
                            <p class="text-[11px] text-slate-400">
                                Total: {{ $totalKnowledge }} dokumen tersimpan
                                @if(!is_null($searchCount))
                                    &nbsp;·&nbsp;
                                    <span class="font-semibold text-indigo-600">{{ $searchCount }} hasil ditemukan</span>
                                    untuk <em>"{{ request('search') }}"</em>
                                @endif
                            </p>
                        </div>

                        {{-- Filter & Search Form yang ditingkatkan --}}
                        <form method="GET" action="{{ route('admin.dashboard') }}"
                            x-data="{ searchVal: '{{ request('search') }}' }"
                            class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">

                            {{-- Filter Klaster --}}
                            <select name="cluster" onchange="this.form.submit()"
                                class="text-xs rounded-xl border border-slate-200 px-2.5 py-2 bg-white text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">Semua Klaster</option>
                                @foreach($availableClusters as $c)
                                    <option value="{{ $c }}" {{ request('cluster') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>

                            {{-- Scope Pencarian --}}
                            <select name="search_scope"
                                class="text-xs rounded-xl border border-slate-200 px-2.5 py-2 bg-white text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all"        {{ request('search_scope', 'all') === 'all'      ? 'selected' : '' }}>Cari: Semua Kolom</option>
                                <option value="topic"      {{ request('search_scope') === 'topic'           ? 'selected' : '' }}>Cari: Topik Saja</option>
                                <option value="keywords"   {{ request('search_scope') === 'keywords'        ? 'selected' : '' }}>Cari: Kata Kunci Saja</option>
                                <option value="content"    {{ request('search_scope') === 'content'         ? 'selected' : '' }}>Cari: Isi Konten</option>
                            </select>

                            {{-- Input Pencarian --}}
                            <div class="relative flex items-center">
                                <svg class="absolute left-3 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="search" name="search"
                                    x-model="searchVal"
                                    value="{{ request('search') }}"
                                    placeholder="Cari topik, kata kunci, atau isi konten..."
                                    @search="$el.closest('form').submit()"
                                    class="text-xs rounded-xl border border-slate-200 pl-8 pr-8 py-2 w-full sm:w-64 focus:border-indigo-500 focus:ring-indigo-500 focus:w-72 transition-all duration-200 [&::-webkit-search-cancel-button]:hidden">
                                {{-- Tombol clear --}}
                                <button type="button"
                                    x-show="searchVal.length > 0"
                                    @click="searchVal = ''; $nextTick(() => $el.closest('form').submit())"
                                    class="absolute right-2 text-slate-400 hover:text-slate-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- Tombol Cari --}}
                            <button type="submit"
                                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Cari
                            </button>

                            {{-- Tombol Reset --}}
                            @if(request('search') || (request('cluster') && request('cluster') !== 'all'))
                                <a href="{{ route('admin.dashboard') }}"
                                    title="Reset semua filter"
                                    class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-all flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Klaster</th>
                                    <th class="px-4 py-3 font-semibold">Topik</th>
                                    <th class="px-4 py-3 font-semibold">Pratinjau Isi</th>
                                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($knowledges as $item)
                                    <tr class="hover:bg-slate-50/80 transition-all">
                                        <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold 
                                                {{ $item->cluster === 'Pendidikan' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $item->cluster === 'Perizinan' ? 'bg-amber-100 text-amber-700' : '' }}
                                                {{ $item->cluster === 'Kesehatan' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $item->cluster === 'Dinas Damkar' ? 'bg-rose-100 text-rose-700' : '' }}
                                                {{ $item->cluster === 'Pelayanan Publik' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                                {{ $item->cluster === 'Pemerintahan' ? 'bg-slate-100 text-slate-700' : '' }}
                                                {{ $item->cluster === 'Kuliner' ? 'bg-orange-100 text-orange-700' : '' }}
                                                {{ $item->cluster === 'Hotel' ? 'bg-fuchsia-100 text-fuchsia-700' : '' }}
                                                {{ $item->cluster === 'Wisata' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                                {{ $item->cluster === 'Direktori Kota Bandung' ? 'bg-violet-100 text-violet-700' : '' }}
                                                {{ $item->cluster === 'Kependudukan' ? 'bg-lime-100 text-lime-700' : '' }}
                                                {{ $item->cluster === '112 Kota Bandung' ? 'bg-pink-100 text-pink-700' : '' }}
                                                {{ !in_array($item->cluster, ['Pendidikan', 'Perizinan', 'Kesehatan', 'Dinas Damkar', 'Pelayanan Publik', 'Pemerintahan', 'Kuliner', 'Hotel', 'Wisata', 'Direktori Kota Bandung', 'Kependudukan', '112 Kota Bandung', 'Lainnya']) ? 'bg-slate-100 text-slate-700' : '' }}">
                                                {{ $item->cluster }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-800 max-w-[170px]">
                                            <div class="truncate font-semibold">{{ $item->topic }}</div>
                                            
                                            {{-- Ekstraksi URL sumber dari array keywords secara aman atau dari kolom url --}}
                                            @php
                                                $sourceUrl = $item->url;
                                                if (str_starts_with((string)$sourceUrl, 'manual-')) {
                                                    $sourceUrl = null;
                                                }
                                                
                                                $keywordsArray = [];
                                                if (is_array($item->keywords)) {
                                                    $keywordsArray = $item->keywords;
                                                } elseif (is_string($item->keywords)) {
                                                    $decoded = json_decode($item->keywords, true);
                                                    $keywordsArray = is_array($decoded) ? $decoded : explode(',', $item->keywords);
                                                }

                                                $filteredKeywords = [];
                                                foreach ($keywordsArray as $kw) {
                                                    $kw = trim($kw);
                                                    if (str_starts_with($kw, 'source:')) {
                                                        if (empty($sourceUrl)) {
                                                            $sourceUrl = substr($kw, 7);
                                                        }
                                                    } else {
                                                        $filteredKeywords[] = $kw;
                                                    }
                                                }
                                                $itemKeywords = implode(', ', $filteredKeywords);
                                            @endphp

                                            @if($sourceUrl)
                                                <a href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer" class="block text-[10px] text-indigo-500 hover:underline mt-0.5 truncate max-w-[150px]">Link Sumber ↗</a>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 max-w-[200px] truncate">
                                            {{ Str::limit($item->content, 60) }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            @php
                                                $editPayload = [
                                                    'id'       => $item->id,
                                                    'cluster'  => $item->cluster,
                                                    'topic'    => $item->topic,
                                                    'keywords' => $itemKeywords,
                                                    'url'      => (string) $sourceUrl,
                                                    'content'  => (string) ($item->content ?? ''),
                                                ];
                                            @endphp
                                            <div class="flex items-center justify-end gap-1">
                                                {{-- Tombol Edit --}}
                                                <button type="button"
                                                    onclick="openEditModal({{ \Illuminate\Support\Js::from($editPayload) }})"
                                                    aria-label="Edit knowledge ini"
                                                    title="Edit knowledge ini"
                                                    class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-lg transition-all cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <form x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); return; } if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) { $event.preventDefault(); return; } submitting = true" action="{{ route('admin.knowledge.destroy', $item->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" :disabled="submitting" aria-label="Hapus knowledge ini" title="Hapus knowledge ini" class="p-1.5 bg-rose-50 hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60 text-rose-600 rounded-lg transition-all">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                            Belum ada data knowledge base. Tambahkan data pertama Anda di atas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($knowledges->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                            {{ $knowledges->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kolom Kanan: Pengaturan Bot AI (5/12) -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden lg:sticky lg:top-6">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 text-sm">2. Pengaturan Tampilan Bot</h3>
                        <span class="px-2 py-0.5 text-[10px] bg-slate-200 text-slate-700 rounded-md font-semibold">Personalisasi</span>
                    </div>

                    <form x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); return; } submitting = true" action="{{ route('admin.settings.update') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div>
                            <label for="bot-name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Asisten AI Bot</label>
                            <input id="bot-name" type="text" name="bot_name" value="{{ old('bot_name', $settings['bot_name'] ?? 'Asisten Virtual Diskominfo') }}" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2">
                        </div>

                        <div>
                            <label for="welcome-message" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Pesan Sambutan</label>
                            <textarea id="welcome-message" name="welcome_message" rows="3" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all border border-slate-200 px-3 py-2">{{ old('welcome_message', $settings['welcome_message'] ?? 'Halo! Ada yang bisa saya bantu hari ini?') }}</textarea>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 mb-2">Simulasi Balasan Pertama:</p>
                            <div class="flex gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold">AI</div>
                                <div class="p-2.5 bg-white border border-slate-200 rounded-2xl rounded-tl-none text-xs text-slate-700 max-w-[220px]">
                                    {{ old('welcome_message', $settings['welcome_message'] ?? 'Halo! Ada yang bisa saya bantu hari ini?') }}
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" :disabled="submitting" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 text-white font-medium text-sm rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                <span x-text="submitting ? 'Menyimpan konfigurasi...' : 'Simpan Konfigurasi Bot'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ============================
         MODAL EDIT KNOWLEDGE BASE
    ============================  --}}
    <div
        x-data="{
            open: false,
            submitting: false,
            id: null,
            cluster: '',
            topic: '',
            keywords: '',
            url: '',
            content: '',
            loadData(data) {
                if (!data) return;
                this.id         = data.id;
                this.cluster    = data.cluster || '';
                this.topic      = data.topic || '';
                this.keywords   = data.keywords || '';
                this.url        = data.url || '';
                this.content    = data.content || '';
                this.submitting = false;
                this.open       = true;
            }
        }"
        @open-edit-modal.window="loadData($event.detail)"
        @keydown.escape.window="open = false"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-edit-title"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal panel --}}
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            {{-- Header Modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60 rounded-t-2xl">
                <div>
                    <h3 id="modal-edit-title" class="font-bold text-slate-800 text-sm">Edit Data Knowledge Base</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perubahan akan langsung tersimpan ke database</p>
                </div>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all" aria-label="Tutup modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Form Edit --}}
            <form
                :action="'{{ url('admin/knowledge') }}/' + (id || '')"
                method="POST"
                @submit="if (submitting) { $event.preventDefault(); return; } submitting = true"
                class="p-6 space-y-4"
            >
                @csrf
                @method('PUT')

                {{-- Cluster --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Klaster / Kategori</label>
                    <select name="cluster" x-model="cluster" required class="w-full text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 transition-all">
                        @foreach($availableClusters as $clusterOption)
                            <option value="{{ $clusterOption }}">{{ $clusterOption }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Topic --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Topik / Judul</label>
                    <input type="text" name="topic" x-model="topic" required maxlength="255"
                        class="w-full text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 transition-all"
                        placeholder="Judul dokumen atau layanan">
                </div>

                {{-- Keywords --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Kata Kunci
                        <span class="text-slate-400 font-normal normal-case">(pisahkan dengan koma)</span>
                    </label>
                    <input type="text" name="keywords" x-model="keywords"
                        class="w-full text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 transition-all"
                        placeholder="ktp, kartu keluarga, disdukcapil">
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: ktp, kartu keluarga, nik, pindah domisili</p>
                </div>

                {{-- URL Sumber --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        URL Referensi / Sumber <span class="text-slate-400 font-normal normal-case">(opsional)</span>
                    </label>
                    <input type="text" name="source_url" x-model="url"
                        class="w-full text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 transition-all"
                        placeholder="https://...">
                </div>

                {{-- Content --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Isi Konten</label>
                    <textarea name="content" x-model="content" rows="8" required
                        class="w-full text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 transition-all font-mono text-xs"
                        placeholder="Isi informasi / dokumen layanan..."></textarea>
                    <p class="text-[10px] text-slate-400 mt-1" x-text="'Panjang konten: ' + (content ? content.length : 0) + ' karakter'"></p>
                </div>

                {{-- Footer --}}
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="open = false"
                        class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-medium text-sm rounded-xl transition-all flex items-center justify-center gap-2">
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(data) {
            window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: data }));
        }
    </script>

</x-app-layout>