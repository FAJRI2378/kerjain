<script>
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast } from '$lib/ui/toast.svelte.js';

  // State Daftar Kontak Freelancer untuk Sisi UMKM
  let freelancerChats = $state([
    { 
      id: 1, 
      name: 'Ahmad Rizki (Gold Worker)', 
      avatar: '👨‍💻', 
      isOnline: true,
      closeTime: '22:00 WIB',
      lastSeen: 'Online sekarang',
      whatsapp: '6281234567890',
      lastMessage: 'Halo kak, apakah desain logo Kopi Kenangan Senja sudah sesuai?', 
      time: '10:45',
      unread: 1,
      messages: [
        { sender: 'umkm', text: 'Halo Ahmad, bagaimana progres logo kopinya?', time: '10:30', read: true },
        { sender: 'freelancer', text: 'Halo kak, apakah desain logo Kopi Kenangan Senja sudah sesuai?', time: '10:45', read: true }
      ]
    },
    { 
      id: 2, 
      name: 'Siti Aminah (Platinum)', 
      avatar: '👩‍🎨', 
      isOnline: false,
      closeTime: '18:00 WIB',
      lastSeen: 'Terakhir aktif 2 jam lalu',
      whatsapp: '6289876543210',
      lastMessage: 'Baik kak, feeds Instagram toko baju sudah saya jadwalkan.', 
      time: 'Kemarin',
      unread: 0,
      messages: [
        { sender: 'umkm', text: 'Tolong pastikan tema warna feeds-nya konsisten ya.', time: 'Kemarin', read: true },
        { sender: 'freelancer', text: 'Baik kak, feeds Instagram toko baju sudah saya jadwalkan.', time: 'Kemarin', read: true }
      ]
    }
  ]);

  let activeChat = $state(freelancerChats[0]);
  let newMessageText = $state('');

  // Fungsi Kirim Pesan dengan Status Centang (1 / 2)
  function sendMessage(e) {
    e.preventDefault();
    if (!newMessageText.trim()) return;

    const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Tambah pesan baru dari sisi UMKM (read: false artinya centang 1)
    activeChat.messages.push({
      sender: 'umkm',
      text: newMessageText,
      time: timeNow,
      read: false 
    });

    activeChat.lastMessage = newMessageText;
    activeChat.time = timeNow;
    newMessageText = '';

    // Simulasi jika freelancer online, pesan otomatis terbaca (centang 2 biru) setelah 1.5 detik
    if (activeChat.isOnline) {
      setTimeout(() => {
        const lastMsg = activeChat.messages[activeChat.messages.length - 1];
        if (lastMsg && lastMsg.sender === 'umkm') {
          lastMsg.read = true;
        }
      }, 1500);
    }
  }

  function selectChat(chat) {
    activeChat = chat;
    chat.unread = 0;
  }

  // Fungsi Alihkan ke WhatsApp jika Freelancer sedang Offline
  function redirectToWhatsApp(chat) {
    const url = `https://wa.me/${chat.whatsapp}?text=Halo%20${encodeURIComponent(chat.name)},%20saya%20menghubungi%20Anda%20melalui%20platform%20Kerjain.`;
    window.open(url, '_blank');
  }
</script>

<div class="chat-page font-sans">
  <div class="chat-container card">
    <!-- Kolom Kiri: Daftar Kontak Freelancer -->
    <div class="chat-sidebar">
      <div class="sidebar-header">
        <h2>Pesan Freelancer</h2>
        <span class="chat-badge-count">{freelancerChats.length} Mitra</span>
      </div>

      <div class="contact-list">
        {#each freelancerChats as chat}
          <!-- svelte-ignore a11y_click_events_have_key_events -->
          <!-- svelte-ignore a11y_no_static_element_interactions -->
          <div 
            class="contact-item {activeChat.id === chat.id ? 'active' : ''}"
            onclick={() => selectChat(chat)}
          >
            <div class="avatar-wrapper">
              <div class="contact-avatar">{chat.avatar}</div>
              <span class="status-dot {chat.isOnline ? 'online' : 'offline'}"></span>
            </div>
            
            <div class="contact-info">
              <div class="contact-head">
                <h4 class="contact-name">{chat.name}</h4>
                <span class="contact-time">{chat.time}</span>
              </div>
              <p class="contact-last-msg">{chat.lastMessage}</p>
            </div>

            {#if chat.unread > 0}
              <span class="unread-dot">{chat.unread}</span>
            {/if}
          </div>
        {/each}
      </div>
    </div>

    <!-- Kolom Kanan: Ruang Obrolan (Chat Room) -->
    <div class="chat-room">
      {#if activeChat}
        <!-- Header Chat Room dengan Info Status -->
        <div class="room-header">
          <div class="room-user">
            <span class="room-avatar">{activeChat.avatar}</span>
            <div>
              <div class="room-title-row">
                <h3 class="room-name">{activeChat.name}</h3>
                <span class="status-badge {activeChat.isOnline ? 'badge-on' : 'badge-off'}">
                  {activeChat.isOnline ? '🟢 Online' : '🔴 Offline'}
                </span>
              </div>
              <p class="room-status-sub">
                {activeChat.isOnline ? `Aktif sampai pukul ${activeChat.closeTime}` : activeChat.lastSeen}
              </p>
            </div>
          </div>

          <!-- Tombol Alihkan ke WhatsApp jika Offline -->
          {#if !activeChat.isOnline}
            <button class="btn-whatsapp" onclick={() => redirectToWhatsApp(activeChat)} title="Hubungi via WhatsApp">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.124-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
              Chat WhatsApp
            </button>
          {/if}
        </div>

        <!-- Daftar Pesan & Status Centang (1 / 2) -->
        <div class="room-messages">
          {#each activeChat.messages as msg}
            <div class="message-bubble {msg.sender === 'umkm' ? 'outgoing' : 'incoming'}">
              <p class="msg-text">{msg.text}</p>
              
              <div class="msg-footer">
                <span class="msg-time">{msg.time}</span>
                {#if msg.sender === 'umkm'}
                  <span class="read-receipt {msg.read ? 'read' : ''}" title={msg.read ? 'Dibaca' : 'Terkirim'}>
                    {msg.read ? '✓✓' : '✓'}
                  </span>
                {/if}
              </div>
            </div>
          {/each}
        </div>

        <!-- Form Kirim Pesan -->
        <form onsubmit={sendMessage} class="room-input-form">
          <input 
            type="text" 
            bind:value={newMessageText} 
            placeholder={activeChat.isOnline ? "Ketik pesan instruksi tugas..." : "Freelancer sedang offline, Anda bisa mengalihkan ke WhatsApp..."} 
            class="chat-input"
          />
          <button type="submit" class="btn-send-chat">Kirim ➔</button>
        </form>
      {:else}
        <div class="no-chat-selected">
          <p>Pilih salah satu freelancer di sebelah kiri untuk mulai berdiskusi.</p>
        </div>
      {/if}
    </div>
  </div>
</div>

<style>
  .chat-page {
    padding: 32px 24px;
    max-width: 1200px;
    margin: 0 auto;
    height: calc(100vh - 40px);
  }

  .chat-container {
    display: flex;
    height: 100%;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  }

  /* Sidebar Kontak */
  .chat-sidebar {
    width: 350px;
    border-right: 1px solid #334155;
    display: flex;
    flex-direction: column;
    background: #0f172a;
  }

  .sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #334155;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .sidebar-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #ffffff;
  }

  .chat-badge-count {
    font-size: 11px;
    font-weight: 700;
    background: #334155;
    color: #94a3b8;
    padding: 2px 8px;
    border-radius: 10px;
  }

  .contact-list {
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    cursor: pointer;
    border-bottom: 1px solid #334155;
    transition: background 0.2s;
    position: relative;
  }

  .contact-item:hover {
    background: #1e293b;
  }

  .contact-item.active {
    background: rgba(99, 102, 241, 0.15);
    border-left: 4px solid #6366f1;
  }

  .avatar-wrapper {
    position: relative;
    flex-shrink: 0;
  }

  .contact-avatar {
    width: 40px;
    height: 40px;
    background: #1e293b;
    border: 1px solid #475569;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
  }

  .status-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #0f172a;
  }
  .status-dot.online { background: #22c55e; }
  .status-dot.offline { background: #94a3b8; }

  .contact-info {
    flex: 1;
    overflow: hidden;
  }

  .contact-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
  }

  .contact-name {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .contact-time {
    font-size: 10px;
    color: #94a3b8;
  }

  .contact-last-msg {
    margin: 0;
    font-size: 12px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .unread-dot {
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 800;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Ruang Obrolan */
  .chat-room {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #1e293b;
  }

  .room-header {
    padding: 14px 24px;
    border-bottom: 1px solid #334155;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .room-user {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .room-avatar {
    font-size: 24px;
    background: #0f172a;
    padding: 6px;
    border-radius: 8px;
  }

  .room-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .room-name {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #ffffff;
  }

  .status-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
  }
  .badge-on { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
  .badge-off { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }

  .room-status-sub {
    margin: 2px 0 0;
    font-size: 11px;
    color: #94a3b8;
  }

  /* Tombol WhatsApp */
  .btn-whatsapp {
    background: #25d366;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.2s;
  }
  .btn-whatsapp:hover { background: #20ba5a; }

  .room-messages {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #0f172a;
  }

  .message-bubble {
    max-width: 65%;
    padding: 10px 14px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .message-bubble.incoming {
    background: #1e293b;
    border: 1px solid #334155;
    align-self: flex-start;
    border-bottom-left-radius: 2px;
    color: #f8fafc;
  }

  .message-bubble.outgoing {
    background: #4f46e5;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 2px;
  }

  .msg-text {
    margin: 0;
    font-size: 13.5px;
    line-height: 1.4;
  }

  .msg-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
  }

  .msg-time {
    font-size: 9px;
    opacity: 0.7;
  }

  /* Status Centang (1 / 2) */
  .read-receipt {
    font-size: 10px;
    font-weight: bold;
    opacity: 0.7;
  }
  .read-receipt.read {
    color: #93c5fd;
    opacity: 1;
  }

  .room-input-form {
    padding: 16px 24px;
    border-top: 1px solid #334155;
    display: flex;
    gap: 12px;
    background: #1e293b;
  }

  .chat-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #334155;
    border-radius: 10px;
    font-size: 13.5px;
    outline: none;
    background: #0f172a;
    color: #ffffff;
    transition: border-color 0.2s;
  }

  .chat-input:focus {
    border-color: #6366f1;
  }

  .btn-send-chat {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-send-chat:hover {
    background: #4338ca;
  }

  .no-chat-selected {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 13.5px;
    background: #0f172a;
  }
</style>