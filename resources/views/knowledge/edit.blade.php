<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Knowledge - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="knowledgeForm()">

    <x-header title="Edit Knowledge" placeholder="Cari knowledge..." />

    <!-- SIDEBAR -->
    <x-sidebar />

    <!-- CONTENT -->
    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-2xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('knowledge.index') }}" class="hover:text-blue-600 transition">Knowledge Base</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Edit</span>
        </nav>

        <!-- CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            <!-- HEADER -->
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500
                            flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Edit Knowledge</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Perbarui kata kunci, jawaban, atau lampiran
                    </p>
                </div>
            </div>

            <!-- FORM -->
            <form action="{{ route('knowledge.update', $knowledge) }}" method="POST" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PUT')

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
                        <input type="text" name="kata_kunci" value="{{ old('kata_kunci', $knowledge->kata_kunci) }}"
                            class="w-full pl-10 pr-4 py-3 border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('kata_kunci') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                   transition"
                            required>
                    </div>
                    @error('kata_kunci')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jawaban -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">
                        Jawaban <span class="text-red-500">*</span>
                    </label>
                    <textarea name="jawaban" rows="6"
                        class="w-full px-4 py-3 border rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('jawaban') border-red-400 bg-red-50 @else border-gray-200 @enderror
                               resize-none transition"
                        required>{{ old('jawaban', $knowledge->jawaban) }}</textarea>
                    @error('jawaban')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- FILE LAMA -->
                @if ($knowledge->file_path)
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Lampiran Saat Ini
                        </label>
                        @php
                            $cat = match (true) {
                                str_starts_with($knowledge->file_type ?? '', 'image/') => 'image',
                                $knowledge->file_type === 'application/pdf' => 'pdf',
                                str_contains($knowledge->file_type ?? '', 'word') => 'word',
                                str_contains($knowledge->file_type ?? '', 'excel') ||
                                    str_contains($knowledge->file_type ?? '', 'spreadsheet')
                                    => 'excel',
                                str_contains($knowledge->file_type ?? '', 'powerpoint') ||
                                    str_contains($knowledge->file_type ?? '', 'presentation')
                                    => 'powerpoint',
                                str_contains($knowledge->file_type ?? '', 'zip') ||
                                    str_contains($knowledge->file_type ?? '', 'rar')
                                    => 'archive',
                                default => 'other',
                            };
                            $icon = match ($cat) {
                                'image' => '🖼️',
                                'pdf' => '📄',
                                'word' => '📝',
                                'excel' => '📊',
                                'powerpoint' => '📽️',
                                'archive' => '📦',
                                default => '📎',
                            };
                            $size = $knowledge->file_size ?? 0;
                            $units = ['B', 'KB', 'MB', 'GB'];
                            $i = 0;
                            while ($size >= 1024 && $i < count($units) - 1) {
                                $size /= 1024;
                                $i++;
                            }
                            $sizeStr = round($size, 1) . ' ' . $units[$i];
                        @endphp

                        {{-- Preview Image --}}
                        @if ($cat === 'image')
                            <img src="{{ asset('storage/' . $knowledge->file_path) }}" alt="{{ $knowledge->file_name }}"
                                class="w-full max-h-64 object-contain rounded-xl border border-gray-200 bg-gray-50 mb-2">
                        @endif

                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                            <span class="text-2xl shrink-0">{{ $icon }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $knowledge->file_name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ strtoupper($cat) }} · {{ $sizeStr }}
                                </p>
                            </div>
                            <a href="{{ asset('storage/' . $knowledge->file_path) }}" target="_blank"
                                class="shrink-0 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200
                                       hover:bg-blue-100 text-xs font-semibold transition">
                                Lihat
                            </a>
                            <button type="button"
                                onclick="document.getElementById('remove_file').checked = true; document.getElementById('fileLama').classList.add('opacity-40', 'line-through')"
                                class="shrink-0 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 border border-red-200
                                       hover:bg-red-100 text-xs font-semibold transition">
                                Hapus
                            </button>
                        </div>

                        {{-- Hidden checkbox untuk hapus file --}}
                        <input type="checkbox" id="remove_file" name="remove_file" value="1" class="hidden">
                        <p class="text-xs text-gray-500 mt-1.5">
                            💡 Klik <strong>Hapus</strong> lalu Update untuk menghapus lampiran ini.
                        </p>
                    </div>
                @endif

                <!-- UPLOAD FILE BARU -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">
                        {{ $knowledge->file_path ? 'Ganti Lampiran (Opsional)' : 'Lampiran (Opsional)' }}
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
                            <input type="file" name="file"
                                accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,.rar,.7z"
                                class="hidden" onchange="previewFile(event)">
                        </label>
                        <span class="text-xs text-gray-500">
                            JPG, PNG, PDF, Word, Excel, PPT, ZIP · Maks 10 MB
                        </span>
                    </div>

                    @error('file')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror

                    <!-- Preview file baru -->
                    <div id="filePreview" class="mt-3 hidden">
                        <div class="p-3 border border-gray-200 rounded-xl bg-gray-50 flex items-center gap-3">
                            <img id="imagePreviewEl" src="#" alt="Preview"
                                class="max-h-16 rounded-lg border border-gray-200 hidden">
                            <div id="pdfPreviewEl" class="hidden">
                                <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="fileNameEl" class="text-sm font-semibold text-gray-800 truncate"></p>
                                <p id="fileSizeEl" class="text-xs text-gray-500"></p>
                            </div>
                            <button type="button" onclick="clearFile()"
                                class="shrink-0 p-1.5 rounded-lg hover:bg-red-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- INFO UPDATE -->
                <div class="flex items-start gap-2 p-3 rounded-lg bg-blue-50 border border-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-blue-700">
                        Perubahan akan langsung berlaku pada chatbot setelah disimpan.
                    </p>
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
                        Update
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

    <script>
        function previewFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            const preview = document.getElementById('filePreview');
            const imgEl = document.getElementById('imagePreviewEl');
            const pdfEl = document.getElementById('pdfPreviewEl');
            const nameEl = document.getElementById('fileNameEl');
            const sizeEl = document.getElementById('fileSizeEl');

            preview.classList.remove('hidden');
            nameEl.textContent = file.name;
            sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

            if (file.type === 'application/pdf') {
                imgEl.classList.add('hidden');
                pdfEl.classList.remove('hidden');
            } else if (file.type.startsWith('image/')) {
                pdfEl.classList.add('hidden');
                imgEl.classList.remove('hidden');
                imgEl.src = URL.createObjectURL(file);
            } else {
                imgEl.classList.add('hidden');
                pdfEl.classList.remove('hidden');
            }
        }

        function clearFile() {
            document.querySelector('input[name="file"]').value = '';
            document.getElementById('filePreview').classList.add('hidden');
        }

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

        // 🆕 pageLayout — handle sidebar collapse
        function pageLayout() {
            return {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                init() {
                    window.addEventListener('sidebar-toggled', (e) => {
                        this.collapsed = e.detail.collapsed;
                    });
                }
            }
        }
    </script>

</body>

</html>
