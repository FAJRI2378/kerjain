import React, { useState } from 'react';
import { 
  Briefcase, 
  MapPin, 
  Clock, 
  Plus, 
  Search, 
  Building2, 
  ChevronRight, 
  X, 
  CheckCircle2,
  Menu
} from 'lucide-react';

const INITIAL_JOBS = [
  {
    id: 1,
    umkm: 'Soto Ayam Pak Budi',
    category: 'Konten & Media',
    title: 'Foto Produk 20 Menu + Upload ke Gofood',
    duration: '3 Jam',
    budget: 75000,
    distance: '1.4 km',
    location: 'Tebet, Jakarta Selatan',
    status: 'Buka',
    tags: ['Foto', 'Content Creator', 'Entry Data'],
    description: 'Dibutuhkan anak muda atau mahasiswa yang paham mengambil foto makanan aesthetic menggunakan HP.'
  },
  {
    id: 2,
    umkm: 'Kopi Kenangan Lokal',
    category: 'Desain',
    title: 'Desain Poster Promo Grand Opening (A3)',
    duration: '2 Jam',
    budget: 50000,
    distance: '0.8 km',
    location: 'Pancasila, Depok',
    status: 'Buka',
    tags: ['Canva', 'Desain Gratis'],
    description: 'Buat poster promo beli 1 gratis 1 yang menarik untuk dipasang di depan kedai kopi.'
  },
  {
    id: 3,
    umkm: 'Toko Berkah Kelontong',
    category: 'Administrasi',
    title: 'Bantu Input Stok Barang ke Excel/Aplikasi',
    duration: '4 Jam',
    budget: 100000,
    distance: '2.1 km',
    location: 'Margonda, Depok',
    status: 'Buka',
    tags: ['Excel', 'Input Data', 'Teliti'],
    description: 'Rapikan stok sembako yang baru datang dan catat ke dalam file Excel toko.'
  },
  {
    id: 4,
    umkm: 'Dapur Mama Snack',
    category: 'Operasional',
    title: 'Bantu Packing 100 Box Snack Box',
    duration: '2.5 Jam',
    budget: 60000,
    distance: '3.0 km',
    location: 'Kukusan, Depok',
    status: 'Buka',
    tags: ['Physical Work', 'Bantu Event'],
    description: 'Bantu packing snack box acara seminar untuk esok pagi.'
  }
];

export default function App() {
  const [activeTab, setActiveTab] = useState('browse');
  const [jobs, setJobs] = useState(INITIAL_JOBS);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('Semua');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  
  // Modal States
  const [isPostModalOpen, setIsPostModalOpen] = useState(false);
  const [selectedJobForApply, setSelectedJobForApply] = useState(null);

  const [formData, setFormData] = useState({
    umkm: '',
    title: '',
    category: 'Konten & Media',
    duration: '',
    budget: '',
    location: '',
    description: ''
  });

  const categories = ['Semua', 'Konten & Media', 'Desain', 'Administrasi', 'Operasional'];

  const filteredJobs = jobs.filter(job => {
    const matchesSearch = job.title.toLowerCase().includes(searchQuery.toLowerCase()) || 
                          job.umkm.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory = selectedCategory === 'Semua' || job.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  const handlePostJob = (e) => {
    e.preventDefault();
    if (!formData.title || !formData.umkm || !formData.budget) return;

    const newJob = {
      id: Date.now(),
      ...formData,
      budget: Number(formData.budget),
      distance: '0.5 km',
      status: 'Buka',
      tags: [formData.category, 'Baru']
    };

    setJobs([newJob, ...jobs]);
    setIsPostModalOpen(false);
    setFormData({ umkm: '', title: '', category: 'Konten & Media', duration: '', budget: '', location: '', description: '' });
  };

  const handleConfirmApply = () => {
    alert(`Berhasil mengambil tugas: "${selectedJobForApply.title}"`);
    setSelectedJobForApply(null);
  };

  return (
    <div className="w-screen min-h-screen bg-[#f8fafc] text-slate-800 font-sans antialiased overflow-x-hidden">
      {/* NAVBAR - Full Width */}
      <header className="sticky top-0 z-40 bg-white border-b border-slate-200 w-full px-4 sm:px-8">
        <div className="w-full h-16 flex items-center justify-between">
          <div className="flex items-center gap-2 sm:gap-3 cursor-pointer" onClick={() => setActiveTab('browse')}>
            <div className="bg-[#0f2337] text-white p-2 rounded-lg">
              <Briefcase className="w-5 h-5" />
            </div>
            <div>
              <span className="text-lg sm:text-xl font-extrabold tracking-tight text-[#0f2337] block leading-none">KERJAIN</span>
              <span className="text-[9px] sm:text-[10px] text-emerald-700 font-medium">Kerja kecil, dampak besar</span>
            </div>
          </div>

          {/* Desktop Nav */}
          <nav className="hidden md:flex items-center gap-4">
            <button 
              onClick={() => setActiveTab('browse')}
              className={`text-sm font-semibold transition ${activeTab === 'browse' ? 'text-[#0f2337] border-b-2 border-emerald-600 py-4' : 'text-slate-500 hover:text-slate-800'}`}
            >
              Cari Pekerjaan
            </button>
            <button 
              onClick={() => setIsPostModalOpen(true)}
              className="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-[#108e66] text-white hover:bg-[#0d7856] transition"
            >
              <Plus className="w-4 h-4" />
              <span>Buka Lowongan</span>
            </button>
          </nav>

          {/* Mobile Menu Toggle Button */}
          <button 
            className="md:hidden p-2 text-slate-600"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          >
            {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>

        {/* Mobile Dropdown Nav */}
        {isMobileMenuOpen && (
          <div className="md:hidden bg-white border-b border-slate-200 py-3 space-y-2">
            <button 
              onClick={() => { setActiveTab('browse'); setIsMobileMenuOpen(false); }}
              className="block w-full text-left py-2 font-semibold text-slate-700 hover:text-[#0f2337]"
            >
              Cari Pekerjaan
            </button>
            <button 
              onClick={() => { setIsPostModalOpen(true); setIsMobileMenuOpen(false); }}
              className="w-full flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold bg-[#108e66] text-white hover:bg-[#0d7856] transition"
            >
              <Plus className="w-4 h-4" />
              <span>Buka Lowongan</span>
            </button>
          </div>
        )}
      </header>

      {/* HERO SECTION - Full Width */}
      {activeTab === 'browse' && (
        <section className="bg-[#0f2337] text-white py-8 sm:py-14 px-4 sm:px-8 w-full">
          <div className="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-center">
            
            {/* Left Column */}
            <div className="lg:col-span-8 space-y-4 sm:space-y-6">
              <h1 className="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">
                Temukan kerja yang dekat dengan <span className="text-[#a3e635]">keahlianmu.</span>
              </h1>
              <p className="text-slate-300 text-xs sm:text-base">
                Ambil pekerjaan singkat dari UMKM sekitar, dapatkan pengalaman nyata, dan bantu bisnis lokal tumbuh.
              </p>

              {/* Search Bar - Wide Layout */}
              <div className="bg-white p-1.5 sm:p-2 rounded-xl shadow-lg w-full flex flex-col sm:flex-row items-center gap-2">
                <div className="w-full flex items-center px-3 py-1">
                  <Search className="w-4 h-4 sm:w-5 sm:h-5 text-slate-400 mr-2 shrink-0" />
                  <input 
                    type="text"
                    placeholder="Cari Foto, Excel, Desain..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="w-full bg-transparent text-slate-800 text-xs sm:text-sm focus:outline-none"
                  />
                </div>
                <button className="w-full sm:w-auto bg-[#108e66] hover:bg-[#0d7856] text-white font-semibold px-6 py-2.5 rounded-lg transition text-xs sm:text-sm whitespace-nowrap">
                  Cari Tugas
                </button>
              </div>
            </div>

            {/* Right Column: Stat Card */}
            <div className="lg:col-span-4 flex justify-start lg:justify-end">
              <div className="bg-[#0b1a29]/60 border border-slate-700/50 p-4 sm:p-6 rounded-2xl w-full space-y-3 sm:space-y-4">
                <div>
                  <div className="text-3xl sm:text-5xl font-black text-[#a3e635]">{jobs.length}+</div>
                  <div className="text-xs sm:text-sm font-semibold text-slate-200 mt-1">tugas mikro siap dikerjakan</div>
                </div>
                <div className="pt-3 border-t border-slate-700/50">
                  <p className="text-xs italic text-slate-300">
                    "Peluang pertama bisa dimulai dari jarak terdekat."
                  </p>
                  <div className="flex items-center justify-between mt-2 text-[10px] sm:text-[11px] text-slate-400">
                    <span className="flex items-center gap-1 text-emerald-400">✓ Aman & transparan</span>
                    <span>SDG #8</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>
      )}

      {/* MAIN CONTENT - Full Width Grid (Multi-column di layar lebar) */}
      <main className="w-full px-4 sm:px-8 py-6 sm:py-10">
        {activeTab === 'browse' && (
          <div>
            {/* Category Filter */}
            <div className="mb-6 sm:mb-8">
              <div className="flex items-center justify-between mb-3 sm:mb-4">
                <div>
                  <span className="text-[10px] sm:text-xs font-bold text-emerald-600 tracking-wider uppercase">Peluang Terbaru</span>
                  <h2 className="text-xl sm:text-2xl font-bold text-slate-900">Tugas di sekitarmu</h2>
                </div>
                <span className="text-xs text-slate-500 font-medium">{filteredJobs.length} tugas tersedia</span>
              </div>

              {/* Category Pills */}
              <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                {categories.map((cat) => (
                  <button
                    key={cat}
                    onClick={() => setSelectedCategory(cat)}
                    className={`px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition ${
                      selectedCategory === cat
                        ? 'bg-[#0f2337] text-white'
                        : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    {cat}
                  </button>
                ))}
              </div>
            </div>

            {/* Grid Kartu Responsif: Menyesuaikan Lebar Layar (1 Kolom HP, 2 Kolom Tablet, 3-4 Kolom Desktop) */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
              {filteredJobs.length > 0 ? (
                filteredJobs.map((job) => (
                  <div key={job.id} className="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                      <div className="flex items-start justify-between gap-2 mb-2 sm:mb-3">
                        <span className="text-[10px] sm:text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md">
                          {job.category}
                        </span>
                        <span className="text-[10px] sm:text-xs text-emerald-600 font-bold flex items-center gap-1">
                          <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {job.status}
                        </span>
                      </div>

                      <h3 className="font-bold text-base sm:text-lg text-slate-900 mb-1.5 sm:mb-2 leading-snug">{job.title}</h3>

                      <div className="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-500 mb-3">
                        <span className="flex items-center gap-1 font-medium text-slate-700">
                          <Building2 className="w-3.5 h-3.5 text-slate-400" /> {job.umkm}
                        </span>
                        <span>•</span>
                        <span className="flex items-center gap-1">
                          <MapPin className="w-3.5 h-3.5 text-slate-400" /> {job.distance} - {job.location}
                        </span>
                      </div>

                      <p className="text-slate-600 text-xs leading-relaxed mb-3 sm:mb-4 line-clamp-2">
                        {job.description}
                      </p>

                      <div className="flex flex-wrap gap-1 mb-4 sm:mb-6">
                        {job.tags.map((tag, idx) => (
                          <span key={idx} className="bg-slate-100 text-slate-500 text-[10px] sm:text-[11px] font-medium px-2 py-0.5 rounded">
                            #{tag}
                          </span>
                        ))}
                      </div>
                    </div>

                    <div className="pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-between">
                      <div>
                        <div className="text-[9px] sm:text-[10px] text-slate-400 uppercase font-bold tracking-wider">Imbalan</div>
                        <div className="text-sm sm:text-lg font-black text-slate-900">
                          Rp {job.budget.toLocaleString('id-ID')}
                          <span className="text-[10px] sm:text-xs font-normal text-slate-500 block sm:inline sm:ml-1">
                            <Clock className="w-3 h-3 inline mr-0.5" />{job.duration}
                          </span>
                        </div>
                      </div>
                      <button 
                        onClick={() => setSelectedJobForApply(job)}
                        className="bg-[#0f2337] hover:bg-[#108e66] text-white text-xs font-semibold px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg transition flex items-center gap-1"
                      >
                        Ambil Tugas <ChevronRight className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                ))
              ) : (
                <div className="col-span-full py-12 text-center text-xs sm:text-sm text-slate-500 bg-white rounded-2xl border border-dashed border-slate-200">
                  Pekerjaan mikro tidak ditemukan. Coba ubah kata kunci atau kategori.
                </div>
              )}
            </div>
          </div>
        )}
      </main>

      {/* MODAL: POST JOB */}
      {isPostModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
          <div className="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 relative shadow-2xl max-h-[90vh] overflow-y-auto">
            <button 
              onClick={() => setIsPostModalOpen(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="mb-4 sm:mb-5">
              <span className="text-[10px] font-bold uppercase text-emerald-600 tracking-wider">Untuk Pemilik UMKM</span>
              <h3 className="text-lg sm:text-xl font-extrabold text-slate-900">Pasang lowongan baru</h3>
              <p className="text-xs text-slate-500">Temukan bantuan fleksibel dari talenta sekitar dalam hitungan menit.</p>
            </div>

            <form onSubmit={handlePostJob} className="space-y-3 text-xs">
              <div>
                <label className="block font-bold text-slate-700 mb-1">Nama usaha</label>
                <input 
                  type="text" 
                  required
                  placeholder="Contoh: Warung Bakso Mas Giri" 
                  value={formData.umkm}
                  onChange={(e) => setFormData({...formData, umkm: e.target.value})}
                  className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 mb-1">Judul pekerjaan</label>
                <input 
                  type="text" 
                  required
                  placeholder="Contoh: Foto 10 menu makanan" 
                  value={formData.title}
                  onChange={(e) => setFormData({...formData, title: e.target.value})}
                  className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-slate-700 mb-1">Kategori</label>
                  <select 
                    value={formData.category}
                    onChange={(e) => setFormData({...formData, category: e.target.value})}
                    className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600 bg-white"
                  >
                    <option value="Konten & Media">Konten & Media</option>
                    <option value="Desain">Desain</option>
                    <option value="Administrasi">Administrasi</option>
                    <option value="Operasional">Operasional</option>
                  </select>
                </div>

                <div>
                  <label className="block font-bold text-slate-700 mb-1">Durasi</label>
                  <input 
                    type="text" 
                    required
                    placeholder="3 Jam" 
                    value={formData.duration}
                    onChange={(e) => setFormData({...formData, duration: e.target.value})}
                    className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-slate-700 mb-1">Budget (Rp)</label>
                  <input 
                    type="number" 
                    required
                    placeholder="75000" 
                    value={formData.budget}
                    onChange={(e) => setFormData({...formData, budget: e.target.value})}
                    className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600"
                  />
                </div>

                <div>
                  <label className="block font-bold text-slate-700 mb-1">Lokasi</label>
                  <input 
                    type="text" 
                    required
                    placeholder="Tebet, Jakarta Selatan" 
                    value={formData.location}
                    onChange={(e) => setFormData({...formData, location: e.target.value})}
                    className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600"
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold text-slate-700 mb-1">Deskripsi tugas</label>
                <textarea 
                  rows={3}
                  required
                  placeholder="Jelaskan detail tugas dan kebutuhan..."
                  value={formData.description}
                  onChange={(e) => setFormData({...formData, description: e.target.value})}
                  className="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-600"
                ></textarea>
              </div>

              <div className="flex gap-2 pt-3">
                <button 
                  type="button" 
                  onClick={() => setIsPostModalOpen(false)}
                  className="flex-1 py-2.5 border border-slate-200 text-slate-600 font-semibold rounded-lg hover:bg-slate-50 transition"
                >
                  Batal
                </button>
                <button 
                  type="submit"
                  className="flex-1 py-2.5 bg-[#108e66] text-white font-semibold rounded-lg hover:bg-[#0d7856] transition"
                >
                  Publikasikan Lowongan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: APPLY CONFIRMATION */}
      {selectedJobForApply && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
          <div className="bg-white rounded-2xl max-w-sm w-full p-5 sm:p-6 text-center shadow-2xl">
            <div className="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
              <CheckCircle2 className="w-6 h-6" />
            </div>

            <span className="text-[10px] font-bold uppercase text-emerald-600 tracking-wider">Konfirmasi Tugas</span>
            <h3 className="text-lg sm:text-xl font-extrabold text-slate-900 mt-1">Siap mengambil tugas ini?</h3>
            
            <div className="bg-slate-50 p-3 rounded-xl my-4 text-xs text-slate-600">
              <p className="font-bold text-slate-800">"{selectedJobForApply.title}"</p>
              <p className="text-emerald-700 font-extrabold mt-1">
                Rp {selectedJobForApply.budget.toLocaleString('id-ID')} <span className="font-normal text-slate-500">• {selectedJobForApply.duration}</span>
              </p>
            </div>

            <div className="flex gap-2">
              <button 
                onClick={() => setSelectedJobForApply(null)}
                className="flex-1 py-2.5 border border-slate-200 text-slate-600 font-semibold rounded-lg text-xs hover:bg-slate-50 transition"
              >
                Kembali
              </button>
              <button 
                onClick={handleConfirmApply}
                className="flex-1 py-2.5 bg-[#108e66] text-white font-semibold rounded-lg text-xs hover:bg-[#0d7856] transition"
              >
                Ya, Ambil Tugas
              </button>
            </div>
          </div>
        </div>
      )}

      {/* FOOTER - Full Width */}
      <footer className="mt-12 sm:mt-20 bg-[#0f2337] text-white py-8 w-full px-4 sm:px-8">
        <div className="w-full flex flex-col md:flex-row items-center justify-between text-xs text-slate-400 gap-4 text-center md:text-left">
          <div>
            <span className="font-bold text-white">KERJAIN</span> — Platform kerja mikro untuk Indonesia.
          </div>
          <div>
            © 2026 • Dibuat untuk UMKM dan talenta lokal
          </div>
        </div>
      </footer>
    </div>
  );
}