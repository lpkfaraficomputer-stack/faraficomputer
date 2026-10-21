{{-- ============ Agen AI Penjawab Konsumen (widget mengambang) ============ --}}
<div class="agent-widget" id="agentWidget">
    <button type="button" class="agent-launcher" id="agentLauncher" aria-label="Buka asisten Farafi">
        <span class="agent-launcher-icon">💬</span>
        <span class="agent-launcher-close">✕</span>
        <span class="agent-notif-dot" id="agentNotifDot"></span>
    </button>

    <section class="agent-panel" id="agentPanel" aria-live="polite" aria-hidden="true">
        <header class="agent-head">
            <div class="agent-head-avatar">🤖</div>
            <div class="agent-head-text">
                <strong>{{ config('services.agent.name') }}</strong>
                <span class="agent-status"><i class="agent-status-dot"></i> Online — siap membantu</span>
            </div>
            <button type="button" class="agent-head-close" id="agentClose" aria-label="Tutup">✕</button>
        </header>

        <div class="agent-body" id="agentBody">
            <div class="agent-msg agent-msg-bot">
                <p>Halo! 👋 Saya <strong>{{ config('services.agent.name') }}</strong>, asisten Farafi Computer.</p>
                <p>Tanya apa saja soal kursus, harga, promo, atau cara pemesanan. 😊</p>
            </div>

            <div class="agent-suggestions" id="agentSuggestions">
                <button type="button" class="agent-chip">Kursus apa saja yang tersedia?</button>
                <button type="button" class="agent-chip">Berapa harga kelas Excel?</button>
                <button type="button" class="agent-chip">Ada promo diskon?</button>
                <button type="button" class="agent-chip">Bagaimana cara memesan kursus?</button>
            </div>
        </div>

        <form class="agent-form" id="agentForm" autocomplete="off">
            <textarea id="agentInput" name="message" rows="1" maxlength="600" placeholder="Tulis pertanyaan Anda..." required></textarea>
            <button type="submit" class="agent-send" id="agentSend" aria-label="Kirim">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 2 9 22 2z"/></svg>
            </button>
        </form>
        <p class="agent-footnote">Jawaban dibuat otomatis oleh AI. Untuk hal penting, konfirmasi lewat WhatsApp kami.</p>
    </section>
</div>

@push('scripts')
<script>
(() => {
    const widget = document.getElementById('agentWidget');
    const launcher = document.getElementById('agentLauncher');
    const panel = document.getElementById('agentPanel');
    const body = document.getElementById('agentBody');
    const form = document.getElementById('agentForm');
    const input = document.getElementById('agentInput');
    const send = document.getElementById('agentSend');
    const suggestions = document.getElementById('agentSuggestions');
    const notifDot = document.getElementById('agentNotifDot');

    const ENDPOINT = @json(route('agent.chat'));
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;
    const history = [];
    let busy = false;

    const toggle = (open) => {
        widget.classList.toggle('open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
            notifDot?.classList.add('hidden');
            setTimeout(() => input.focus(), 220);
        }
    };

    launcher.addEventListener('click', () => toggle(!widget.classList.contains('open')));
    document.getElementById('agentClose').addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggle(false); });

    // ---- render teks: **tebal**, *miring*, `kode`, baris baru ----
    const render = (text) => {
        const escape = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        return escape(text)
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/(^|\s)\*([^*\n]+)\*/g, '$1<em>$2</em>')
            .replace(/\n/g, '<br>');
    };

    const bubble = (text, who) => {
        const el = document.createElement('div');
        el.className = 'agent-msg agent-msg-' + who;
        el.innerHTML = '<p>' + render(text) + '</p>';
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
        return el;
    };

    const typing = () => {
        const el = document.createElement('div');
        el.className = 'agent-msg agent-msg-bot agent-typing';
        el.innerHTML = '<span></span><span></span><span></span>';
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
        return el;
    };

    const ask = async (text) => {
        if (busy) return;
        text = text.trim();
        if (text.length < 2) return;

        busy = true;
        send.disabled = true;
        suggestions?.remove();
        bubble(text, 'user');
        input.value = '';
        input.style.height = 'auto';

        const dots = typing();

        try {
            const res = await fetch(ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message: text, history: history.slice(-8) }),
            });

            const data = await res.json().catch(() => ({}));
            dots.remove();

            const reply = data.message || 'Maaf, saya belum bisa menjawab saat ini. Silakan hubungi WhatsApp kami ya.';
            bubble(reply, 'bot');

            history.push({ role: 'user', content: text });
            history.push({ role: 'assistant', content: reply });
        } catch (err) {
            dots.remove();
            bubble('Koneksi terputus. Mohon periksa jaringan Anda lalu coba lagi. 🙏', 'bot');
        } finally {
            busy = false;
            send.disabled = false;
            input.focus();
        }
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        ask(input.value);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            ask(input.value);
        }
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 110) + 'px';
    });

    suggestions?.querySelectorAll('.agent-chip').forEach((chip) => {
        chip.addEventListener('click', () => ask(chip.textContent));
    });

    // ---- salam otomatis di kunjungan pertama ----
    try {
        if (!localStorage.getItem('farafi_agent_seen')) {
            localStorage.setItem('farafi_agent_seen', '1');
            setTimeout(() => {
                if (!widget.classList.contains('open')) notifDot?.classList.remove('hidden');
            }, 4000);
        }
    } catch (e) { /* localStorage bisa diblokir */ }
})();
</script>
@endpush
