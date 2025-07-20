<template>
    <div class="card">
        <div class="flex justify-between items-center mb-6">
            <h1 class="page-title">Список заказов</h1>
            <router-link to="/orders/create" class="btn btn-primary">
                Создать заказ
            </router-link>
        </div>

        <div class="filter-container mb-4">
            <select v-model="filters.status" class="form-select">
                <option value="">Все статусы</option>
                <option value="active">Активные</option>
                <option value="completed">Завершенные</option>
                <option value="canceled">Отмененные</option>
            </select>

            <select v-model="filters.warehouse_id" class="form-select">
                <option value="">Все склады</option>
                <option v-for="warehouse in warehouses"
                        :key="warehouse.id"
                        :value="warehouse.id">
                    {{ warehouse.name }}
                </option>
            </select>
        </div>

        <table class="table-container">
            <thead class="table-header">
                <tr>
                    <th class="table-header-cell">ID</th>
                    <th class="table-header-cell">Клиент</th>
                    <th class="table-header-cell">Склад</th>
                    <th class="table-header-cell">Статус</th>
                    <th class="table-header-cell">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="order in orders.data" :key="order.id" class="table-row">
                    <td class="table-cell">{{ order.id }}</td>
                    <td class="table-cell">{{ order.customer }}</td>
                    <td class="table-cell">{{ order.warehouse.name }}</td>
                    <td class="table-cell">
                        <span :class="statusClass(order.status)">
                            {{ statusText(order.status) }}
                        </span>
                    </td>
                    <td class="table-cell">
                        <router-link :to="`/orders/${order.id}/edit`"
                                     class="btn btn-secondary mr-3">
                            Редактировать
                        </router-link>
                        <button v-if="order.status === 'active'"
                                @click="completeOrder(order.id)"
                                class="btn btn-success">
                            Завершить
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pagination">
            <button @click="fetchOrders(orders.current_page - 1)"
                    :disabled="orders.current_page === 1"
                    class="pagination-btn">
                Назад
            </button>
            <span>Страница {{ orders.current_page }} из {{ orders.last_page }}</span>
            <button @click="fetchOrders(orders.current_page + 1)"
                    :disabled="orders.current_page === orders.last_page"
                    class="pagination-btn">
                Вперед
            </button>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                orders: { data: [] },
                warehouses: [],
                filters: {
                    status: '',
                    warehouse_id: ''
                }
            }
        },
        methods: {
            statusText(status) {
                const statusMap = {
                    active: 'Активный',
                    completed: 'Завершен',
                    canceled: 'Отменен'
                };
                return statusMap[status] || status;
            },
            statusClass(status) {
                return {
                    'status-badge': true,
                    'status-active': status === 'active',
                    'status-completed': status === 'completed',
                    'status-canceled': status === 'canceled'
                };
            },
            async fetchOrders(page = 1) {
    try {
        this.loading = true;
        const params = {
            page,
            status: this.filters.status,
            warehouse_id: this.filters.warehouse_id
        };
        
        const response = await axios.get('/api/orders', { params });
        
        // Проверяем наличие данных
        if (!response.data?.data) {
            throw new Error('Неверный формат ответа от сервера');
        }
        
        this.orders = response.data;
    } catch (error) {
        console.error('Ошибка загрузки заказов:', error);
        this.$notify({
            type: 'error',
            title: 'Ошибка',
            text: error.response?.data?.message || error.message
        });
    } finally {
        this.loading = false;
    }
},
            async fetchWarehouses() {
                const response = await axios.get('/api/warehouses');
                this.warehouses = response.data.data;
            },
            async completeOrder(id) {
                if (confirm('Вы уверены, что хотите завершить этот заказ?')) {
                    await axios.post(`/api/orders/${id}/complete`);
                    this.fetchOrders();
                }
            }
        },
        watch: {
            filters: {
                handler() {
                    this.fetchOrders();
                },
                deep: true
            }
        },
        created() {
            this.fetchOrders();
            this.fetchWarehouses();
        }
    }
</script>
