<template>
  <div class="card">
    <div v-if="loading" class="text-center p-4">Загрузка данных...</div>
    <div v-else-if="error" class="text-red-500 p-4">{{ error }}</div>
    
    <template v-else-if="order">
      <h1 class="page-title">Редактирование заказа #{{ order.id }}</h1>
      
      <form @submit.prevent="submitForm">
        <div class="form-group">
          <div>
            <label class="form-label">Клиент</label>
            <input 
              v-model="form.customer"
              type="text"
              class="form-input"
              required
            >
          </div>

          <div>
            <label class="form-label">Склад</label>
            <select 
              v-model="form.warehouse_id"
              class="form-select"
              required
            >
              <option value="">Выберите склад</option>
              <option 
                v-for="warehouse in warehouses"
                :key="warehouse.id"
                :value="warehouse.id"
              >
                {{ warehouse.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="mb-6">
          <h2 class="text-lg font-medium mb-4">Позиции заказа</h2>
          
          <div 
            v-for="(item, index) in form.items"
            :key="item.id || index"
            class="grid grid-cols-12 gap-4 mb-4"
          >
            <div class="col-span-5">
              <select 
                v-model="item.product_id"
                class="form-select"
                required
              >
                <option value="">Выберите товар</option>
                <option 
                  v-for="product in products"
                  :key="product.id"
                  :value="product.id"
                >
                  {{ product.name }} ({{ product.price }} ₽)
                </option>
              </select>
            </div>
            <div class="col-span-5">
              <input
                v-model.number="item.count"
                type="number"
                min="1"
                class="form-input"
                placeholder="Количество"
                required
              >
            </div>
            <div class="col-span-2">
              <button
                type="button"
                @click="removeItem(index)"
                class="btn-danger"
              >
                Удалить
              </button>
            </div>
          </div>

          <button
            type="button"
            @click="addItem"
            class="btn btn-secondary"
          >
            Добавить позицию
          </button>
        </div>

        <div class="flex justify-end space-x-4">
          <button
            v-if="order.status === 'active'"
            type="button"
            @click="completeOrder"
            class="btn btn-success"
          >
            Завершить заказ
          </button>
          <button
            type="submit"
            class="btn btn-primary"
          >
            Сохранить изменения
          </button>
        </div>
      </form>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const order = ref(null)
const form = ref({
  customer: '',
  warehouse_id: null,
  items: []
})

const products = ref([])
const warehouses = ref([])
const loading = ref(true)
const error = ref(null)

const fetchOrder = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await axios.get(`/api/orders/${route.params.id}`)
    
    // Check if response is HTML (invalid response)
    if (typeof response.data === 'string' && response.data.startsWith('<!DOCTYPE html>')) {
      throw new Error('Invalid server response - received HTML instead of JSON')
    }
    
    const responseData = response.data?.data || response.data
    if (!responseData?.id) {
      throw new Error('Сервер вернул неполные данные заказа')
    }
    
    order.value = responseData
    form.value = {
      customer: responseData.customer,
      warehouse_id: responseData.warehouse_id,
      items: (responseData.items || []).map(item => ({
        id: item.id,
        product_id: item.product_id,
        count: item.count
      }))
    }
    
  } catch (err) {
    console.error('Order load error:', err)
    error.value = err.response?.data?.message || 
                 err.response?.data?.error || 
                 err.message || 
                 'Неизвестная ошибка при загрузке заказа'
    router.push('/orders')
  } finally {
    loading.value = false
  }
}

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/products')
    products.value = response.data.data || []
  } catch (err) {
    console.error('Error fetching products:', err)
    products.value = []
  }
}

const fetchWarehouses = async () => {
  try {
    const response = await axios.get('/api/warehouses')
    warehouses.value = response.data.data || []
  } catch (err) {
    console.error('Error fetching warehouses:', err)
    warehouses.value = []
  }
}

const addItem = () => {
  form.value.items.push({ product_id: '', count: 1 })
}

const removeItem = (index) => {
  if (form.value.items.length > 1) {
    form.value.items.splice(index, 1)
  }
}

const validateForm = () => {
  const errors = [];
  
  if (!form.value.customer?.trim()) {
    errors.push('Укажите имя клиента');
  }
  
  if (!form.value.warehouse_id) {
    errors.push('Выберите склад');
  }
  
  if (!form.value.items?.length) {
    errors.push('Добавьте хотя бы одну позицию');
  } else {
    form.value.items.forEach((item, index) => {
      if (!item.product_id) {
        errors.push(`Позиция ${index + 1}: выберите товар`);
      }
      if (!item.count || item.count < 1) {
        errors.push(`Позиция ${index + 1}: укажите количество (минимум 1)`);
      }
    });
  }
  
  return errors;
};
const submitForm = async () => {
  try {
    // Подготовка данных в правильном формате
    const payload = {
      customer: form.value.customer,
      warehouse_id: form.value.warehouse_id,
      items: form.value.items.map(item => ({
        id: item.id || null, // null для новых позиций
        product_id: item.product_id,
        count: Number(item.count) // явное преобразование в число
      }))
    };

    console.log('Sending payload:', payload); // Для отладки

    const response = await axios.put(`/api/orders/${order.value.id}`, payload);
    
    console.log('Update successful:', response.data);
    router.push('/orders');
  } catch (err) {
    console.error('Full error details:', {
      message: err.message,
      status: err.response?.status,
      data: err.response?.data,
      validation: err.response?.data?.errors,
      request: err.config?.data
    });
    
    const errorMsg = err.response?.data?.message || 
                    Object.values(err.response?.data?.errors || {}).flat().join('\n') || 
                    err.message;
    alert(`Ошибка обновления:\n${errorMsg}`);
  }
}

const completeOrder = async () => {
  if (confirm('Вы уверены, что хотите завершить этот заказ?')) {
    try {
      await axios.post(`/api/orders/${order.value.id}/complete`)
      router.push('/orders')
    } catch (err) {
      console.error('Error completing order:', err)
      alert('Ошибка при завершении заказа: ' + (err.response?.data?.message || err.message))
    }
  }
}

onMounted(async () => {
  try {
    await fetchOrder()
    await Promise.all([
      fetchProducts(),
      fetchWarehouses()
    ])
  } catch (err) {
    console.error('Component mount error:', err)
  }
})
</script>
