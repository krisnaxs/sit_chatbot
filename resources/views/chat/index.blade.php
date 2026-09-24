<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot - Suralaya Information Center</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== Animated Gradient Background ===== */
        .bg-chat {
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #0f172a, #312e81);
            background-size: 400% 400%;
            animation: gradientShift 18s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* ===== Floating Orbs ===== */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            pointer-events: none;
            animation: float 14s ease-in-out infinite;
            z-index: 0;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: #3b82f6;
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: #8b5cf6;
            bottom: -150px;
            right: -150px;
            animation-delay: -4s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: #06b6d4;
            top: 40%;
            left: 50%;
            animation-delay: -8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(40px, -40px) scale(1.05);
            }

            66% {
                transform: translate(-30px, 30px) scale(0.95);
            }
        }

        /* ===== Glassmorphism ===== */
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        /* ===== Chat Bubble Animation ===== */
        .bubble-in {
            animation: bubbleIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes bubbleIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ===== Typing Indicator ===== */
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: 0.4;
            }

            30% {
                transform: translateY(-8px);
                opacity: 1;
            }
        }

        /* ===== Custom Scrollbar ===== */
        .chat-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .chat-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .chat-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.35);
        }
    </style>
</head>

<body class="bg-chat font-sans min-h-screen overflow-x-hidden">

    <!-- Floating Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- HEADER -->
    <x-header title="Chatbot" placeholder="Cari aplikasi..." />

    <!-- SIDEBAR (hanya muncul kalau login) -->
    <x-sidebar />

    <!-- CHAT WRAPPER — margin reaktif terhadap sidebar collapse -->
    <div x-data="pageLayout()"
        :class="{
            'lg:ml-16': @auth true @else false @endauth && collapsed,
            'lg:ml-64': @auth true @else false @endauth && !collapsed
        }"
        class="relative z-10 transition-all duration-300">

        <!-- CHAT CONTAINER — full height minus header -->
        <div class="max-w-4xl mx-auto px-4 py-6 flex flex-col" style="height: calc(100vh - 80px);">

            <!-- CHAT CARD -->
            <div class="glass rounded-3xl shadow-2xl overflow-hidden flex flex-col flex-1" x-data="chatApp()">

                <!-- Chat Header -->
                <div class="px-6 py-4 border-b border-white/15 flex items-center gap-4 bg-white/5 shrink-0">
                    <!-- Bot Avatar -->
                    <div class="relative shrink-0">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-violet-500
                                    flex items-center justify-center shadow-lg shadow-blue-500/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <span
                            class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-emerald-400
                                     border-2 border-slate-900 shadow-[0_0_10px_rgba(52,211,153,0.9)]"></span>
                    </div>

                    <!-- Bot Info -->
                    <div class="flex-1 min-w-0">
                        <h2 class="text-white font-bold text-lg leading-tight">SIS Assistant</h2>
                        <p class="text-white/60 text-xs flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Online · Siap membantu
                        </p>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div id="chatBox" class="chat-scroll flex-1 overflow-y-auto p-6 space-y-4">

                    <!-- Welcome message -->
                    <template x-if="messages.length === 0">
                        <div class="bubble-in flex items-start gap-3">
                            <div
                                class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-violet-500
                                        flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div class="flex-1 max-w-[80%]">
                                <div
                                    class="bg-white/10 backdrop-blur-md border border-white/15
                                            rounded-2xl rounded-tl-md px-4 py-3
                                            text-white text-sm leading-relaxed">
                                    <p class="font-semibold mb-1">Halo! 👋 Saya SIS Assistant.</p>
                                    <p class="text-white/80">
                                        Saya bisa membantu Anda dengan informasi tentang aplikasi di
                                        Suralaya Information Center. Silakan tanya apa saja!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- History + New messages -->
                    <template x-for="(msg, idx) in messages" :key="idx">
                        <div>
                            <!-- User Message (kanan) -->
                            <template x-if="msg.role === 'user'">
                                <div class="bubble-in flex items-start gap-3 justify-end">
                                    <div class="flex-1 max-w-[80%] flex flex-col items-end">
                                        <div
                                            class="bg-gradient-to-br from-blue-500 to-violet-600
                                                    rounded-2xl rounded-tr-md px-4 py-3
                                                    text-white text-sm leading-relaxed
                                                    shadow-lg shadow-blue-500/30">
                                            <p x-text="msg.text" class="whitespace-pre-wrap break-words"></p>
                                        </div>
                                        <p class="text-[10px] text-white/40 mt-1 px-1" x-text="msg.time"></p>
                                    </div>
                                    <div
                                        class="shrink-0 w-9 h-9 rounded-full bg-white/15 backdrop-blur-md
                                                border border-white/20 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </template>

                            <!-- Bot Message (kiri) -->
                            <template x-if="msg.role === 'bot'">
                                <div class="bubble-in flex items-start gap-3">
                                    <div
                                        class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-violet-500
                                                flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 max-w-[80%]">
                                        <div
                                            class="bg-white/10 backdrop-blur-md border border-white/15
                                                    rounded-2xl rounded-tl-md px-4 py-3
                                                    text-white text-sm leading-relaxed">
                                            <p x-text="msg.text" class="whitespace-pre-wrap break-words"></p>

                                            {{-- 📎 FILE ATTACHMENT --}}
                                            <template x-if="msg.file">
                                                <div class="mt-3 pt-3 border-t border-white/15">

                                                    {{-- Preview image --}}
                                                    <template x-if="msg.file.category === 'image'">
                                                        <div>
                                                            <img :src="msg.file.url" :alt="msg.file.name"
                                                                class="max-w-full rounded-lg border border-white/20 shadow-lg mb-2">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs text-white/60 truncate flex-1"
                                                                    x-text="msg.file.name"></span>
                                                                <a :href="msg.file.url" :download="msg.file.name"
                                                                    target="_blank"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5
                                                                           bg-white/15 hover:bg-white/25
                                                                           rounded-lg text-xs font-semibold
                                                                           transition shrink-0">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-3 w-3" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor"
                                                                        stroke-width="2.5">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
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
                                                            class="flex items-center gap-3 p-3
                                                                   bg-red-500/20 hover:bg-red-500/30
                                                                   border border-red-400/30
                                                                   rounded-xl transition">
                                                            <div
                                                                class="shrink-0 w-10 h-10 rounded-lg bg-red-500/30
                                                                        flex items-center justify-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-5 w-5 text-red-200" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor"
                                                                    stroke-width="2">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-xs font-semibold text-white truncate"
                                                                    x-text="msg.file.name"></p>
                                                                <p class="text-[10px] text-white/60">
                                                                    PDF ·
                                                                    <span x-text="formatSize(msg.file.size)"></span>
                                                                </p>
                                                            </div>
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="h-4 w-4 text-white/60 shrink-0" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor"
                                                                stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                                                            </svg>
                                                        </a>
                                                    </template>

                                                    {{-- File Generic (Word, Excel, PPT, ZIP, dll) --}}
                                                    <template x-if="!['image', 'pdf'].includes(msg.file.category)">
                                                        <a :href="msg.file.url" :download="msg.file.name"
                                                            target="_blank"
                                                            class="flex items-center gap-3 p-3
                                                                   bg-white/10 hover:bg-white/20
                                                                   border border-white/20
                                                                   rounded-xl transition">
                                                            <div
                                                                class="shrink-0 w-10 h-10 rounded-lg bg-white/20
                                                                        flex items-center justify-center text-lg">
                                                                <span x-text="fileIcon(msg.file.category)"></span>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-xs font-semibold text-white truncate"
                                                                    x-text="msg.file.name"></p>
                                                                <p class="text-[10px] text-white/60">
                                                                    <span
                                                                        x-text="msg.file.category.toUpperCase()"></span>
                                                                    · <span x-text="formatSize(msg.file.size)"></span>
                                                                </p>
                                                            </div>
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="h-4 w-4 text-white/60 shrink-0" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor"
                                                                stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                                                            </svg>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 px-1">
                                            <p class="text-[10px] text-white/40" x-text="msg.time"></p>
                                            <template x-if="msg.sumber">
                                                <span
                                                    class="text-[9px] px-1.5 py-0.5 rounded-full uppercase font-bold tracking-wider"
                                                    :class="msg.sumber === 'database' ?
                                                        'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' :
                                                        'bg-violet-500/20 text-violet-300 border border-violet-400/30'"
                                                    x-text="msg.sumber === 'database' ? 'KB' : 'AI'">
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Typing Indicator -->
                    <template x-if="isTyping">
                        <div class="bubble-in flex items-start gap-3">
                            <div
                                class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-violet-500
                                        flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div
                                class="bg-white/10 backdrop-blur-md border border-white/15
                                        rounded-2xl rounded-tl-md px-4 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- Chat Input -->
                <div class="px-6 py-4 border-t border-white/15 bg-white/5 shrink-0">
                    <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                        <div class="flex-1 relative">
                            <textarea x-model="input" @keydown.enter.prevent="sendMessage()" @keydown.shift.enter="input += '\n'"
                                :disabled="isTyping" rows="1" placeholder="Ketik pesan Anda..."
                                class="w-full bg-white/10 border border-white/20 rounded-2xl
                                       px-4 py-3 pr-12
                                       text-white placeholder-white/40
                                       text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent
                                       resize-none
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                       transition"
                                style="max-height: 120px;"
                                oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 120) + 'px';"></textarea>
                        </div>

                        <button type="submit" :disabled="isTyping || !input.trim()"
                            class="shrink-0 w-12 h-12 rounded-2xl
                                   bg-gradient-to-br from-blue-500 to-violet-600
                                   hover:from-blue-600 hover:to-violet-700
                                   text-white
                                   flex items-center justify-center
                                   shadow-lg shadow-blue-500/40
                                   transition-all duration-300
                                   hover:scale-105 active:scale-95
                                   disabled:opacity-40 disabled:cursor-not-allowed
                                   disabled:hover:scale-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>

                    <p class="text-white/40 text-[11px] mt-2 text-center">
                        Tekan <kbd class="px-1 py-0.5 bg-white/10 rounded text-white/60 font-mono">Enter</kbd> untuk
                        kirim
                        · <kbd class="px-1 py-0.5 bg-white/10 rounded text-white/60 font-mono">Shift+Enter</kbd> untuk
                        baris baru
                    </p>
                </div>
            </div>

            <!-- Footer Note -->
            <p class="text-center text-white/40 text-xs mt-4">
                SIS Assistant · Powered by Knowledge Base &amp; AI
            </p>
        </div>

    </div>

    <script>
        function chatApp() {
            return {
                input: '',
                messages: [],
                isTyping: false,

                init() {
                    @if (isset($history) && $history->count() > 0)
                        @foreach ($history as $chat)
                            this.messages.push({
                                role: 'user',
                                text: @json($chat->pesan),
                                time: @json(\Carbon\Carbon::parse($chat->waktu)->format('H:i')),
                                sumber: null,
                                file: null,
                            });

                            @php
                                // 🔑 Hitung data file di PHP, bukan di JavaScript
                                $fileData = null;
                                if ($chat->file_path) {
                                    $fileData = [
                                        'url' => asset('storage/' . $chat->file_path),
                                        'name' => $chat->file_name,
                                        'type' => $chat->file_type,
                                        'size' => $chat->file_size,
                                        'category' => \App\Models\Chat::detectCategory($chat->file_type),
                                    ];
                                }
                            @endphp

                            this.messages.push({
                                role: 'bot',
                                text: @json($chat->jawaban),
                                time: @json(\Carbon\Carbon::parse($chat->waktu)->format('H:i')),
                                sumber: @json($chat->file_path ? 'database' : null),
                                file: @json($fileData),
                            });
                        @endforeach
                    @endif

                    this.$nextTick(() => this.scrollToBottom());
                },

                async sendMessage() {
                    const text = this.input.trim();
                    if (!text || this.isTyping) return;

                    this.messages.push({
                        role: 'user',
                        text: text,
                        time: new Date().toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        }),
                        sumber: null,
                        file: null,
                    });

                    this.input = '';
                    this.isTyping = true;

                    this.$nextTick(() => {
                        const ta = document.querySelector('textarea');
                        if (ta) ta.style.height = 'auto';
                        this.scrollToBottom();
                    });

                    try {
                        const response = await fetch("{{ route('chat.send') }}", {
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

                        const data = await response.json();
                        await new Promise(r => setTimeout(r, 400));

                        if (response.ok && data.status === 'ok') {
                            this.messages.push({
                                role: 'bot',
                                text: data.jawaban,
                                time: new Date(data.waktu).toLocaleTimeString('id-ID', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }),
                                sumber: data.sumber || null,
                                file: data.file || null,
                            });
                        } else {
                            this.messages.push({
                                role: 'bot',
                                text: 'Maaf, terjadi kesalahan. Coba lagi nanti.',
                                time: new Date().toLocaleTimeString('id-ID', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }),
                                sumber: null,
                                file: null,
                            });
                        }
                    } catch (err) {
                        this.messages.push({
                            role: 'bot',
                            text: 'Maaf, tidak bisa terhubung ke server. Cek koneksi Anda.',
                            time: new Date().toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }),
                            sumber: null,
                            file: null,
                        });
                    } finally {
                        this.isTyping = false;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                },

                scrollToBottom() {
                    const box = document.getElementById('chatBox');
                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
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
