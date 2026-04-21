<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Detail Denda: {{ $user->name }}</h1>
                    <p class="mt-2 text-slate-600 font-medium text-lg">Total Belum Lunas: <span class="text-rose-600 font-black">Rp {{ number_format($unpaidSum, 0, ',', '.') }}</span></p>
                </div>
                <a href="{{ route('admin.fines.index') }}" class="inline-flex items-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 transition-all">
                    ← Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- FORM TAMBAH DENDA -->
                <div class="lg:col-span-1">
                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Tambah Denda Baru</h2>
                        <form method="POST" action="{{ route('admin.fines.store', $user) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Jumlah (Rp)</label>
                                <input type="number" name="amount" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 10000" required>
                                @error('amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Alasan</label>
                                <textarea name="reason" rows="4" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Jelaskan alasan denda..." required></textarea>
                                @error('reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="w-full rounded-2xl bg-slate-900 px-6 py-4 text-sm font-bold text-white shadow-xl hover:bg-slate-800 transition-all transform active:scale-95">
                                Simpan Denda
                            </button>
                        </form>
                    </div>
                </div>

                <!-- HISTORI DENDA -->
                <div class="lg:col-span-2">
                    <div class="space-y-6">
                        <h2 class="text-xl font-bold text-slate-900">Histori Denda</h2>
                        @forelse($fines as $fine)
                            <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm ring-1 {{ $fine->status === 'unpaid' ? 'ring-rose-100 border-l-4 border-rose-500' : 'ring-slate-200 border-l-4 border-emerald-500' }} transition-all">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-lg font-black text-slate-900">Rp {{ number_format($fine->amount, 0, ',', '.') }}</span>
                                            @if($fine->status === 'paid')
                                                <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[10px] font-black text-emerald-600 uppercase">LUNAS</span>
                                            @else
                                                <span class="rounded-lg bg-rose-50 px-2 py-0.5 text-[10px] font-black text-rose-600 uppercase tracking-widest animate-pulse">BELUM LUNAS</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed font-medium">"{{ $fine->reason }}"</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <form method="POST" action="{{ route('admin.fines.destroy', $fine) }}" onsubmit="return confirm('Hapus data denda ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition-all p-1" title="Hapus Denda">
                                                🗑️
                                            </button>
                                        </form>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $fine->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>

                                @if($fine->status === 'unpaid')
                                    <div class="flex justify-end pt-4 border-t border-slate-50">
                                        <form method="POST" action="{{ route('admin.fines.pay', $fine) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-600 shadow-lg shadow-emerald-100 transition-all transform active:scale-95">
                                                Tandai Lunas
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-right pt-2 border-t border-slate-50 text-[10px] text-emerald-500 font-bold">
                                        Dilunasi pada: {{ $fine->paid_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                                <p class="text-slate-400 font-medium">Belum ada histori denda untuk user ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
