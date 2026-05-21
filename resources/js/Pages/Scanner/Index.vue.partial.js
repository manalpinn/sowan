import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash-es';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Html5Qrcode } from 'html5-qrcode';

// Note: defineProps is a Vue macro and only works in .vue files.
// If this file is used as a partial, these variables should be passed in or defined.
const props = {
  eventId: null,
  events: []
};

const selectedEventId = ref(props.eventId || (props.events?.length > 0 ? props.events[0].id : null));
const html5QrCode = ref(null);
const isScanning = ref(false);
const lastResult = ref(null);
const history = ref([]);
const cameraError = ref(null);

const isOfflineMode = ref(false);
const downloading = ref(false);
const syncing = ref(false);
const offlineGuests = ref([]);
const offlineQueue = ref(JSON.parse(localStorage.getItem('offline_queue') || '[]'));

// Manual token input
const manualToken = ref('');
const processingToken = ref(false);

// Manual search
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const processingManual = ref(null);

const cooldownActive = ref(false);
const cooldownTimer = ref(0);

onMounted(() => {
  startScanner();
  loadOfflineData();
});

onBeforeUnmount(() => {
  stopScanner();
});

watch(selectedEventId, () => {
  loadOfflineData();
});

const loadOfflineData = () => {
  if (!selectedEventId.value) return;
  const data = localStorage.getItem(`offline_data_${selectedEventId.value}`);
  if (data) {
    offlineGuests.value = JSON.parse(data);
  } else {
    offlineGuests.value = [];
  }
};

const startScanner = () => {
  cameraError.value = null;
  html5QrCode.value = new Html5Qrcode("reader");
  
  const config = { fps: 10, qrbox: { width: 250, height: 250 } };
  
  html5QrCode.value.start(
    { facingMode: "environment" },
    config,
    onScanSuccess,
    onScanFailure
  ).then(() => {
    isScanning.value = true;
  }).catch(err => {
    console.error("Camera error:", err);
    cameraError.value = "Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan.";
    isScanning.value = false;
  });
};

const stopScanner = async () => {
  if (html5QrCode.value && html5QrCode.value.isScanning) {
    await html5QrCode.value.stop();
    isScanning.value = false;
  }
};

const toggleScanner = () => {
  if (isScanning.value) stopScanner();
  else startScanner();
};

const onScanSuccess = (decodedText) => {
  if (cooldownActive.value || (lastResult.value && lastResult.value.processing)) return;
  processScan(decodedText);
};

const onScanFailure = (error) => {};

const processScan = async (qrCode) => {
  if (!selectedEventId.value) {
    handleError({ message: 'Silakan pilih event terlebih dahulu.' });
    return;
  }

  // Beep
  try {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(880, context.currentTime);
    gain.gain.setValueAtTime(0.1, context.currentTime);
    oscillator.start();
    oscillator.stop(context.currentTime + 0.1);
  } catch (e) {}

  if (isOfflineMode.value) {
    processOfflineScan(qrCode);
    return;
  }

  lastResult.value = { processing: true, message: 'Memproses...' };
  try {
    const response = await axios.post(route('scanner.scan'), {
      qr_code: qrCode,
      event_id: selectedEventId.value
    });
    handleSuccess(response.data);
    startCooldown();
  } catch (err) {
    handleError(err.response?.data || { message: 'Gagal memproses QR Code.' });
    startCooldown(1500);
  }
};

const processOfflineScan = (qrCode) => {
  const guest = offlineGuests.value.find(g => g.qr_code === qrCode);
  if (!guest) {
    handleError({ message: 'Tamu tidak ditemukan dalam database lokal.' });
    startCooldown(1500);
    return;
  }

  if (offlineQueue.value.some(q => q.qr_code === qrCode)) {
    handleError({ message: `${guest.name} sudah ada dalam antrean sinkronisasi.` });
    startCooldown(1500);
    return;
  }

  const result = {
    guest: guest,
    message: `(Offline) Berhasil: ${guest.name}`
  };

  offlineQueue.value.push({ qr_code: qrCode, time: new Date().toISOString() });
  localStorage.setItem('offline_queue', JSON.stringify(offlineQueue.value));
  handleSuccess(result);
  startCooldown();
};

const startCooldown = (ms = 2500) => {
  cooldownActive.value = true;
  cooldownTimer.value = ms / 1000;
  const interval = setInterval(() => {
    cooldownTimer.value -= 0.1;
    if (cooldownTimer.value <= 0) {
      clearInterval(interval);
      cooldownActive.value = false;
      cooldownTimer.value = 0;
    }
  }, 100);
};

const handleSuccess = (data) => {
  lastResult.value = { success: true, status: data.status, message: data.message };
  history.value.unshift({
    success: true,
    name: data.guest?.name || 'Tamu',
    message: data.message,
    time: new Date().toLocaleTimeString('id-ID'),
    guest: data.guest
  });
  setTimeout(() => { if (lastResult.value?.message === data.message) lastResult.value = null; }, 4000);
};

const handleError = (data) => {
  lastResult.value = { success: false, message: data.message };
  history.value.unshift({
    success: false,
    name: 'Gagal',
    message: data.message,
    time: new Date().toLocaleTimeString('id-ID')
  });
  setTimeout(() => { if (lastResult.value?.message === data.message) lastResult.value = null; }, 4000);
};

const downloadForOffline = async () => {
  if (!selectedEventId.value) return alert('Silakan pilih event terlebih dahulu.');
  downloading.value = true;
  try {
    const response = await axios.post(route('scanner.offline.download'), { event_id: selectedEventId.value });
    offlineGuests.value = response.data.guests;
    localStorage.setItem(`offline_data_${selectedEventId.value}`, JSON.stringify(response.data.guests));
    alert(`Berhasil mendownload ${response.data.guests.length} tamu.`);
  } catch (err) { alert('Gagal mendownload data.'); }
  finally { downloading.value = false; }
};

const syncOfflineData = async () => {
  if (offlineQueue.value.length === 0) return;
  if (!selectedEventId.value) return alert('Silakan pilih event terlebih dahulu.');
  syncing.value = true;
  try {
    await axios.post(route('scanner.offline.sync'), { event_id: selectedEventId.value, checkins: offlineQueue.value });
    offlineQueue.value = [];
    localStorage.setItem('offline_queue', JSON.stringify([]));
    alert('Sinkronisasi berhasil!');
  } catch (err) { alert('Gagal sinkronisasi.'); }
  finally { syncing.value = false; }
};

const processManualToken = async () => {
  const token = manualToken.value.trim().toUpperCase();
  if (token.length !== 5) return handleError({ message: 'Token harus 5 karakter.' });
  processingToken.value = true;
  try { await processScan(token); manualToken.value = ''; }
  finally { processingToken.value = false; }
};

const onSearchInput = debounce(async () => {
  if (searchQuery.value.length < 2) { searchResults.value = []; return; }
  isSearching.value = true;
  try {
    const response = await axios.post(route('scanner.search'), { name: searchQuery.value, event_id: selectedEventId.value });
    searchResults.value = response.data.guests;
  } catch (err) {} finally { isSearching.value = false; }
}, 300);

const processManual = async (guestId) => {
  processingManual.value = guestId;
  try {
    const response = await axios.post(route('scanner.manual'), { guest_id: guestId });
    handleSuccess(response.data);
    onSearchInput();
  } catch (err) { handleError(err.response?.data || { message: 'Gagal.' }); }
  finally { processingManual.value = null; }
};
