import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const statusEl = document.getElementById('status');
const ordersEl = document.getElementById('orders');

function setStatus(message, type = 'info') {
    if (!statusEl) return;
    const colors = {
        info: 'text-blue-700',
        success: 'text-green-700',
        error: 'text-red-700',
    };

    statusEl.className = `text-sm font-medium ${colors[type] ?? colors.info}`;
    statusEl.textContent = message;
}

function appendOrder(order) {
    if (!ordersEl) return;

    const li = document.createElement('li');
    li.className = 'border rounded-md px-3 py-2 bg-gray-50';

    const createdAt = order.created_at ?? new Date().toISOString();

    li.innerHTML = `
        <div class="flex justify-between items-center mb-1">
            <span class="font-semibold">أوردر #${order.id ?? 'غير معروف'}</span>
            <span class="text-xs text-gray-500">${createdAt}</span>
        </div>
        <div class="text-xs text-gray-700">
            <div>العميل: ${order.user?.name ?? 'غير معروف'}</div>
            <div>الإجمالي: ${order.total_price ?? order.total ?? 'غير معروف'}</div>
            <div>الحالة: ${order.status ?? 'غير معروف'}</div>
        </div>
    `;

    ordersEl.prepend(li);
}

async function init() {
    try {
        const meta = document.querySelector('meta[name="kitchen-token"]');
        const token = meta?.getAttribute('content') ?? '';

        if (!token) {
            setStatus('لم يتم العثور على التوكن في الصفحة.', 'error');
            return;
        }

        axios.defaults.headers.common['Authorization'] = token;
        axios.defaults.headers.common['Accept'] = 'application/json';

        setStatus('جاري جلب بيانات المطبخ بالتوكن...', 'info');

        const profileResponse = await axios.get('/api/my-kitchen-profile');
        const profile = profileResponse?.data?.data;

        if (!profile || !profile.id) {
            setStatus('لم أستطع تحديد معرف المطبخ من /api/my-kitchen-profile.', 'error');
            return;
        }

        const kitchenId = profile.id;
        setStatus(`تم الاتصال بالمطبخ (ID = ${kitchenId}). جاري الاشتراك في القناة...`, 'success');


        const echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    Authorization: token,
                },
            },
        });

        echo.private(`kitchen.${kitchenId}`)
            .listen('.new-order', (e) => {
                const order = e.order ?? e;
                appendOrder(order);
            })
            .error((error) => {
                console.error('Channel error', error);
                setStatus('حدث خطأ أثناء الاشتراك في القناة الخاصة بالمطبخ.', 'error');
            });

        setStatus('تم الاشتراك في قناة المطبخ بنجاح. أي أوردر جديد سيظهر هنا فوراً.', 'success');
    } catch (error) {
        console.error(error);
        setStatus('حدث خطأ أثناء تهيئة صفحة المطبخ. تحقق من التوكن أو الـ API.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', init);

