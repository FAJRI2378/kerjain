<script>
  let searchQuery = $state('');
  let roleFilter = $state('all'); // 'all', 'freelancer', 'hirer'

  let users = $state([
    { id: 'USR-01', name: 'Budi Santoso', email: 'budi@gmail.com', role: 'freelancer', verified: true, status: 'Active', joined: '2026-01-10' },
    { id: 'USR-02', name: 'Siti Rahmawati', email: 'siti@gmail.com', role: 'freelancer', verified: false, status: 'Active', joined: '2026-02-14' },
    { id: 'USR-03', name: 'Kopi Hits Nusantara', email: 'admin@kopihits.com', role: 'hirer', verified: true, status: 'Active', joined: '2026-03-01' },
    { id: 'USR-04', name: 'Agus Setiawan', email: 'agus@yahoo.com', role: 'freelancer', verified: false, status: 'Suspended', joined: '2026-04-12' }
  ]);

  let filteredUsers = $derived(
    users.filter(user => {
      const matchSearch = user.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                          user.email.toLowerCase().includes(searchQuery.toLowerCase());
      const matchRole = roleFilter === 'all' || user.role === roleFilter;
      return matchSearch && matchRole;
    })
  );

  function toggleVerify(id) {
    users = users.map(u => u.id === id ? { ...u, verified: !u.verified } : u);
  }

  function toggleStatus(id) {
    users = users.map(u => u.id === id ? { ...u, status: u.status === 'Active' ? 'Suspended' : 'Active' } : u);
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Manajemen Pengguna</h1>
      <p class="text-xs md:text-sm text-slate-400">Verifikasi identitas dan kelola hak akses Freelancer / UMKM.</p>
    </div>
    <div class="text-xs font-semibold text-slate-400 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
      Total User: <span class="text-purple-400 font-bold">{users.length}</span>
    </div>
  </div>

  <!-- Search and Role Selector -->
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <input 
      type="text" 
      placeholder="Cari nama atau email pengguna..." 
      bind:value={searchQuery}
      class="w-full sm:w-80 px-4 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition"
    />

    <div class="flex gap-1.5 bg-slate-900/80 p-1 border border-slate-800 rounded-xl text-xs w-full sm:w-auto">
      <button 
        onclick={() => roleFilter = 'all'} 
        class={`px-3 py-1.5 rounded-lg transition ${roleFilter === 'all' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Semua Role
      </button>
      <button 
        onclick={() => roleFilter = 'freelancer'} 
        class={`px-3 py-1.5 rounded-lg transition ${roleFilter === 'freelancer' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Freelancer
      </button>
      <button 
        onclick={() => roleFilter = 'hirer'} 
        class={`px-3 py-1.5 rounded-lg transition ${roleFilter === 'hirer' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30 font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        UMKM / Hirer
      </button>
    </div>
  </div>

  <!-- Users Table -->
  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-800 bg-slate-950/50 text-slate-400 uppercase font-bold tracking-wider">
            <th class="p-4">Pengguna</th>
            <th class="p-4">Peran (Role)</th>
            <th class="p-4">Status ID (Verifikasi)</th>
            <th class="p-4">Status Akun</th>
            <th class="p-4 text-right">Aksi Moderasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 text-slate-300">
          {#each filteredUsers as user (user.id)}
            <tr class="hover:bg-slate-800/30 transition">
              <td class="p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-purple-400 text-xs">
                  {user.name.charAt(0)}
                </div>
                <div>
                  <p class="font-bold text-white text-sm">{user.name}</p>
                  <p class="text-[10px] text-slate-400">{user.email}</p>
                </div>
              </td>
              <td class="p-4">
                {#if user.role === 'freelancer'}
                  <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg font-bold text-[10px]">🛠️ Freelancer</span>
                {:else}
                  <span class="px-2.5 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-lg font-bold text-[10px]">🏢 UMKM</span>
                {/if}
              </td>
              <td class="p-4">
                {#if user.verified}
                  <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold text-[11px]">
                    ✓ Verified ID
                  </span>
                {:else}
                  <span class="inline-flex items-center gap-1 text-amber-400 font-semibold text-[11px]">
                    ⏳ Belum Verifikasi
                  </span>
                {/if}
              </td>
              <td class="p-4">
                {#if user.status === 'Active'}
                  <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded text-[10px] font-bold">Aktif</span>
                {:else}
                  <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 rounded text-[10px] font-bold">Ditangguhkan</span>
                {/if}
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    onclick={() => toggleVerify(user.id)}
                    class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-lg transition"
                  >
                    {user.verified ? 'Batal Verifikasi' : 'Verifikasi ID'}
                  </button>
                  <button 
                    onclick={() => toggleStatus(user.id)}
                    class={`px-2.5 py-1.5 font-bold rounded-lg transition ${user.status === 'Active' ? 'bg-rose-500/20 text-rose-300 hover:bg-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30'}`}
                  >
                    {user.status === 'Active' ? 'Suspend' : 'Aktifkan'}
                  </button>
                </div>
              </td>
            </tr>
          {:else}
            <tr>
              <td colspan="5" class="p-8 text-center text-slate-500">
                Pengguna tidak ditemukan.
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
</div>