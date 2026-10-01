@extends('layouts.app')

@section('title', 'Tambah Knowledge - Admin')

@section('content')
    <div x-data="knowledgeForm()">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('knowledge.index') }}" class="hover:text-blue-600 transition">Knowledge Base</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Tambah</span>
        </nav>

        <!-- CARD -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

                <!-- HEADER -->
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                            flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Tambah Knowledge</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Tambahkan pertanyaan, jawaban, & lampiran untuk chatbot
                        </p>
                    </div>
                </div>

                <!-- FORM -->
                <form action="{{ route('knowledge.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-5">
                    @csrf

                    <!-- Kata Kunci -->
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Kata Kunci <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <input type="text" name="kata_kunci" value="{{ old('kata_kunci') }}"
                                placeholder="cth: cara login maximoo"
                                class="w-full pl-10 pr-4 py-3 border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('kata_kunci') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                   transition"
                                required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">
                            💡 Gunakan kata kunci yang spesifik dan mudah diingat
                        </p>
                        @error('kata_kunci')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jawaban -->
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Jawaban <span class="text-red-500">*</span>
                        </label>
                        <textarea name="jawaban" rows="6" placeholder="Ketik jawaban yang akan diberikan chatbot..."
                            class="w-full px-4 py-3 border rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('jawaban') border-red-400 bg-red-50 @else border-gray-200 @enderror
                               resize-none transition"
                            required>{{ old('jawaban') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1.5">
                            💡 Berikan jawaban yang jelas, singkat, dan informatif
                        </p>
                        @error('jawaban')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ============================================================ -->
                    <!-- UPLOAD FILE — MULTIPLE (TANPA BATAS JUMLAH) -->
                    <!-- ============================================================ -->
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Lampiran (Opsional)
                        </label>

                        <div class="flex items-center gap-3 flex-wrap">
                            <label
                                class="inline-flex items-center gap-2
                                      bg-gradient-to-br from-blue-500 to-violet-600
                                      hover:from-blue-600 hover:to-violet-700
                                      text-white px-4 py-2.5 rounded-xl
                                      cursor-pointer font-semibold text-sm
                                      shadow-lg shadow-blue-500/30
                                      transition-all duration-300
                                      hover:scale-105 active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Pilih File
                                <input type="file" name="files[]" id="filesInput" multiple
                                    accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,.rar,.7z"
                                    class="hidden" onchange="previewFiles(event)">
                            </label>
                            <button type="button" onclick="clearAllFiles()" id="clearAllBtn"
                                class="hidden items-center gap-2 px-3 py-2.5 rounded-xl
                                       border border-red-300 text-red-600 text-sm font-semibold
                                       hover:bg-red-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Hapus Semua
                            </button>
                            <span class="text-xs text-gray-500">
                                JPG, PNG, PDF, Word, Excel, PPT, ZIP · Maks 500 MB/file
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 mt-1.5">
                            💡 Bisa pilih banyak file sekaligus (Ctrl/Cmd + klik). Semua file akan muncul di chat saat user
                            bertanya dengan kata kunci ini.
                        </p>

                        @error('files')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                        @error('files.*')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror

                        <!-- ============================================================ -->
                        <!-- PREVIEW DAFTAR FILE -->
                        <!-- ============================================================ -->
                        <div id="filesPreview" class="mt-4 hidden">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-700">
                                    File terpilih: <span id="filesCount" class="text-blue-600">0</span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Total: <span id="filesTotalSize">0 B</span>
                                </p>
                            </div>
                            <div id="filesList" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>
                        </div>
                    </div>

                    <!-- BUTTONS -->
                    <div class="flex gap-3 pt-4 border-t border-gray-100">
                        <button type="submit"
                            class="inline-flex items-center gap-2
                               bg-gradient-to-br from-blue-500 to-violet-600
                               hover:from-blue-600 hover:to-violet-700
                               text-white px-5 py-2.5 rounded-xl
                               font-semibold text-sm
                               shadow-lg shadow-blue-500/30
                               transition-all duration-300
                               hover:scale-105 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan
                        </button>
                        <a href="{{ route('knowledge.index') }}"
                            class="px-5 py-2.5 rounded-xl
                               border border-gray-300
                               text-gray-700 font-semibold text-sm
                               hover:bg-gray-50
                               transition">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 🔔 TOAST ERROR VALIDASI -->
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="fixed top-24 right-6 z-[100] flex items-start gap-3
               min-w-[320px] max-w-md
               px-4 py-3 rounded-xl
               bg-red-600 text-white
               shadow-2xl shadow-red-500/50
               border border-red-400/40
               backdrop-blur-md"
            style="display: none;">

            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1">
                <div class="text-sm font-bold mb-1">Validasi gagal</div>
                <ul class="text-xs space-y-0.5 list-disc list-inside opacity-90">
                    <template x-for="(msg, i) in toast.messages" :key="i">
                        <li x-text="msg"></li>
                    </template>
                </ul>
            </div>

            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ============================================================
        // PREVIEW MULTIPLE FILES
        // ============================================================
        function previewFiles(event) {
            const input = event.target;
            const files = Array.from(input.files);

            const preview = document.getElementById('filesPreview');
            const list = document.getElementById('filesList');
            const countEl = document.getElementById('filesCount');
            const totalEl = document.getElementById('filesTotalSize');
            const clearBtn = document.getElementById('clearAllBtn');

            if (!files.length) {
                preview.classList.add('hidden');
                clearBtn.classList.add('hidden');
                clearBtn.classList.remove('inline-flex');
                list.innerHTML = '';
                return;
            }

            preview.classList.remove('hidden');
            clearBtn.classList.remove('hidden');
            clearBtn.classList.add('inline-flex');

            countEl.textContent = files.length;

            let totalSize = 0;
            list.innerHTML = '';

            files.forEach((file, idx) => {
                totalSize += file.size;

                const sizeStr = formatSize(file.size);
                const icon = getFileIcon(file.type, file.name);

                const row = document.createElement('div');
                row.className =
                    'flex items-center gap-3 p-3 border border-gray-200 rounded-xl bg-gray-50 hover:bg-gray-100 transition';

                // Untuk gambar, tampilkan thumbnail
                let thumbHtml = '';
                if (file.type.startsWith('image/')) {
                    const url = URL.createObjectURL(file);
                    thumbHtml =
                        `<img src="${url}" class="w-10 h-10 object-cover rounded-lg border border-gray-200" alt="thumb">`;
                } else {
                    thumbHtml =
                        `<div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-lg">${icon}</div>`;
                }

                row.innerHTML = `
                    ${thumbHtml}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">${escapeHtml(file.name)}</p>
                        <p class="text-xs text-gray-500">${sizeStr}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                        #${idx + 1}
                    </span>
                `;

                list.appendChild(row);
            });

            totalEl.textContent = formatSize(totalSize);
        }

        // ============================================================
        // CLEAR ALL FILES
        // ============================================================
        function clearAllFiles() {
            const input = document.getElementById('filesInput');
            input.value = '';

            document.getElementById('filesPreview').classList.add('hidden');
            const clearBtn = document.getElementById('clearAllBtn');
            clearBtn.classList.add('hidden');
            clearBtn.classList.remove('inline-flex');
            document.getElementById('filesList').innerHTML = '';
        }

        // ============================================================
        // HELPER: Format size
        // ============================================================
        function formatSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            let size = bytes;
            while (size >= 1024 && i < units.length - 1) {
                size /= 1024;
                i++;
            }
            return size.toFixed(2) + ' ' + units[i];
        }

        // ============================================================
        // HELPER: Icon berdasarkan tipe file
        // ============================================================
        function getFileIcon(mime, name) {
            if (mime.startsWith('image/')) return '🖼️';
            if (mime === 'application/pdf') return '📄';
            if (mime.includes('word') || mime.includes('document')) return '📝';
            if (mime.includes('excel') || mime.includes('spreadsheet') || mime === 'text/csv') return '📊';
            if (mime.includes('powerpoint') || mime.includes('presentation')) return '📽️';
            if (mime.includes('zip') || mime.includes('rar') || mime.includes('7z')) return '📦';
            if (mime.startsWith('text/')) return '📃';
            return '📎';
        }

        // ============================================================
        // HELPER: Escape HTML biar aman
        // ============================================================
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // ============================================================
        // ALPINE COMPONENT
        // ============================================================
        function knowledgeForm() {
            return {
                toast: {
                    show: false,
                    messages: [],
                },

                showToast(messages) {
                    this.toast.messages = Array.isArray(messages) ? messages : [messages];
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 5000);
                },

                init() {
                    @if ($errors->any())
                        this.showToast(@json($errors->all()));
                    @endif
                }
            }
        }
    </script>
@endpush
