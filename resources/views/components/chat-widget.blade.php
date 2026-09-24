@props(['context' => 'portal'])

{{-- 🎈 FLOATING CHAT WIDGET — FAB + Modal Mini --}}
<div x-data="chatWidget()" x-init="context = '{{ $context }}'">

    {{-- 🎈 FLOATING BUTTON (FAB) --}}
    <button type="button" @click="openChat()"
        class="chat-fab fixed bottom-6 right-6 z-[60] group
               flex items-center gap-3
               pl-3 pr-5 py-3
               rounded-full
               bg-gradient-to-br from-blue-500 to-violet-600
               hover:from-blue-600 hover:to-violet-700
               text-white font-semibold text-sm
               shadow-2xl shadow-blue-500/40
               transition-all duration-300
               hover:scale-105 active:scale-95
               hover:shadow-blue-500/60"
        title="Tanya SIS Assistant">

        <!-- Avatar bot dengan pulse ring -->
        <div class="relative shrink-0">
            <span class="absolute inset-0 rounded-full bg-white/40 animate-ping opacity-75"></span>
            <div
                class="relative w-9 h-9 rounded-full bg-white/20
                        flex items-center justify-center
                        ring-2 ring-white/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
        </div>

        <!-- Label -->
        <span class="whitespace-nowrap hidden sm:inline">Tanya SIS Assistant</span>
        <span class="whitespace-nowrap sm:hidden">Tanya</span>
    </button>

    {{-- 💬 MODAL CHAT MINI (muncul dari kanan bawah) --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95" @click.away="closeChat()"
        class="fixed bottom-24 right-6 z-[70]
               w-[380px] max-w-[calc(100vw-2rem)]
               bg-white rounded-2xl shadow-2xl border border-gray-100
               overflow-hidden flex flex-col"
        style="height: 560px; max-height: calc(100vh - 8rem); display: none;">

        {{-- HEADER MODAL --}}
        <div
            class="flex items-center justify-between px-4 py-3
                    bg-gradient-to-r from-blue-500 to-violet-600
                    text-white shrink-0">

            <div class="flex items-center gap-3 min-w-0">
                <div class="relative shrink-0">
                    <div
                        class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm
                                flex items-center justify-center ring-2 ring-white/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <span
                        class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400
                                 border-2 border-blue-500"></span>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-sm leading-tight truncate">SIS Assistant</p>
                    <p class="text-[11px] text-white/70 flex items-center gap-1">
                        <span class="w-1 h-1 rounded-full bg-emerald-400"></span>
                        Online · Siap membantu
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                {{-- ⛶ Tombol Expand ke /chat --}}
                <a href="{{ route('chat.index') }}" class="p-1.5 rounded-lg hover:bg-white/20 transition"
                    title="Buka halaman penuh">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </a>

                {{-- ✕ Close --}}
                <button @click="closeChat()" class="p-1.5 rounded-lg hover:bg-white/20 transition" title="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- BODY: Chat Messages --}}
        <div x-ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50 chat-scroll">

            {{-- Welcome message --}}
            <template x-if="messages.length === 0">
                <div class="flex items-start gap-2">
                    <div
                        class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div
                        class="bg-white rounded-2xl rounded-tl-sm px-3.5 py-2.5 shadow-sm border border-gray-100 max-w-[80%]">
                        <p class="text-sm text-gray-700 font-semibold mb-0.5">Halo! 👋</p>
                        <p class="text-sm text-gray-600">
                            Ada yang bisa saya bantu? Tanya apa saja tentang aplikasi di SIT.
                        </p>
                    </div>
                </div>
            </template>

            {{-- Messages --}}
            <template x-for="(msg, idx) in messages" :key="idx">
                <div>
                    {{-- User Message --}}
                    <template x-if="msg.role === 'user'">
                        <div class="flex justify-end">
                            <div
                                class="bg-gradient-to-br from-blue-500 to-violet-600
                                        text-white rounded-2xl rounded-tr-sm
                                        px-3.5 py-2.5 shadow-sm max-w-[80%]">
                                <p class="text-sm whitespace-pre-wrap break-words" x-text="msg.text"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Bot Message --}}
                    <template x-if="msg.role === 'bot'">
                        <div class="flex items-start gap-2">
                            <div
                                class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                        flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div
                                class="bg-white rounded-2xl rounded-tl-sm px-3.5 py-2.5 shadow-sm border border-gray-100 max-w-[80%]">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap break-words" x-text="msg.text">
                                </p>

                                {{-- 📎 FILE ATTACHMENT --}}
                                <template x-if="msg.file">
                                    <div class="mt-2 pt-2 border-t border-gray-100">

                                        {{-- Preview Image --}}
                                        <template x-if="msg.file.category === 'image'">
                                            <div>
                                                <img :src="msg.file.url" :alt="msg.file.name"
                                                    class="max-w-full rounded-lg border border-gray-200 shadow-sm mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] text-gray-500 truncate flex-1"
                                                        x-text="msg.file.name"></span>
                                                    <a :href="msg.file.url" :download="msg.file.name" target="_blank"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1
                                                               bg-blue-50 hover:bg-blue-100 text-blue-700
                                                               border border-blue-200
                                                               rounded-lg text-[11px] font-semibold
                                                               transition shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                                                        </svg>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Preview PDF --}}
                                        <template x-if="msg.file.category === 'pdf'">
                                            <a :href="msg.file.url" target="_blank"
                                                class="flex items-center gap-2 p-2
                                                       bg-red-50 hover:bg-red-100
                                                       border border-red-200
                                                       rounded-xl transition">
                                                <div
                                                    class="shrink-0 w-9 h-9 rounded-lg bg-red-100
                                                            flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 text-red-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-800 truncate"
                                                        x-text="msg.file.name"></p>
                                                    <p class="text-[10px] text-gray-500">
                                                        PDF · <span x-text="formatSize(msg.file.size)"></span>
                                                    </p>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                                                </svg>
                                            </a>
                                        </template>

                                        {{-- File Generic (Word, Excel, PPT, ZIP, dll) --}}
                                        <template x-if="!['image', 'pdf'].includes(msg.file.category)">
                                            <a :href="msg.file.url" :download="msg.file.name" target="_blank"
                                                class="flex items-center gap-2 p-2
                                                       bg-gray-50 hover:bg-gray-100
                                                       border border-gray-200
                                                       rounded-xl transition">
                                                <div
                                                    class="shrink-0 w-9 h-9 rounded-lg bg-white
                                                            border border-gray-200
                                                            flex items-center justify-center text-base">
                                                    <span x-text="fileIcon(msg.file.category)"></span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-800 truncate"
                                                        x-text="msg.file.name"></p>
                                                    <p class="text-[10px] text-gray-500">
                                                        <span x-text="msg.file.category.toUpperCase()"></span>
                                                        · <span x-text="formatSize(msg.file.size)"></span>
                                                    </p>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                                                </svg>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                {{-- Sumber badge --}}
                                <template x-if="msg.sumber">
                                    <span
                                        class="mt-1 inline-block text-[9px] px-1.5 py-0.5 rounded-full uppercase font-bold tracking-wider"
                                        :class="msg.sumber === 'database' ?
                                            'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                            'bg-violet-50 text-violet-700 border border-violet-200'"
                                        x-text="msg.sumber === 'database' ? 'KB' : 'AI'">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <template x-if="isTyping">
                <div class="flex items-start gap-2">
                    <div
                        class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="bg-white rounded-2xl rounded-tl-sm px-3.5 py-3 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-1">
                            <div class="typing-dot bg-gray-400"></div>
                            <div class="typing-dot bg-gray-400"></div>
                            <div class="typing-dot bg-gray-400"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- FOOTER: Input --}}
        <div class="border-t border-gray-100 p-3 bg-white shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                <textarea x-model="input" @keydown.enter.prevent="sendMessage()" @keydown.shift.enter="input += '\n'"
                    :disabled="isTyping" rows="1" placeholder="Ketik pesan..."
                    class="flex-1 bg-gray-50 border border-gray-200 rounded-xl
                           px-3 py-2 text-sm resize-none
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           disabled:opacity-50 transition"
                    style="max-height: 100px;"
                    oninput="this.style.height='auto'; this.style.height=Math.min(this.scrollHeight, 100) + 'px';"></textarea>

                <button type="submit" :disabled="isTyping || !input.trim()"
                    class="shrink-0 w-9 h-9 rounded-xl
                           bg-gradient-to-br from-blue-500 to-violet-600
                           hover:from-blue-600 hover:to-violet-700
                           text-white flex items-center justify-center
                           shadow-md shadow-blue-500/30
                           transition-all duration-200
                           disabled:opacity-40 disabled:cursor-not-allowed
                           disabled:hover:scale-100 hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>

            <p class="text-[10px] text-gray-400 text-center mt-2">
                Klik <span class="font-semibold">⛶</span> untuk buka halaman penuh
            </p>
        </div>
    </div>

    <script>
        function chatWidget() {
            return {
                open: false,
                input: '',
                messages: [],
                isTyping: false,

                openChat() {
                    this.open = true;
                    this.$nextTick(() => this.scrollToBottom());
                },

                closeChat() {
                    this.open = false;
                },

                async sendMessage() {
                    const text = this.input.trim();
                    if (!text || this.isTyping) return;

                    // Tambah pesan user
                    this.messages.push({
                        role: 'user',
                        text: text
                    });
                    this.input = '';
                    this.isTyping = true;
                    this.$nextTick(() => this.scrollToBottom());

                    try {
                        const res = await fetch("{{ route('chat.send') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                pesan: text
                            }),
                        });

                        const data = await res.json();

                        // Delay kecil biar typing indicator kelihatan
                        await new Promise(r => setTimeout(r, 400));

                        if (res.ok && data.status === 'ok') {
                            this.messages.push({
                                role: 'bot',
                                text: data.jawaban,
                                sumber: data.sumber || null,
                                file: data.file || null,
                            });
                        } else {
                            this.messages.push({
                                role: 'bot',
                                text: 'Maaf, terjadi kesalahan. Coba lagi nanti.',
                                file: null,
                            });
                        }
                    } catch (err) {
                        this.messages.push({
                            role: 'bot',
                            text: 'Maaf, tidak bisa terhubung ke server.',
                            file: null,
                        });
                    } finally {
                        this.isTyping = false;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                },

                scrollToBottom() {
                    const box = this.$refs.chatBox;
                    if (box) box.scrollTop = box.scrollHeight;
                },

                formatSize(bytes) {
                    if (!bytes) return '-';
                    const units = ['B', 'KB', 'MB', 'GB'];
                    let i = 0;
                    while (bytes >= 1024 && i < units.length - 1) {
                        bytes /= 1024;
                        i++;
                    }
                    return bytes.toFixed(1) + ' ' + units[i];
                },

                fileIcon(category) {
                    return {
                        'image': '🖼️',
                        'pdf': '📄',
                        'word': '📝',
                        'excel': '📊',
                        'powerpoint': '📽️',
                        'archive': '📦',
                        'other': '📎',
                    } [category] || '📎';
                }
            }
        }
    </script>
</div>
