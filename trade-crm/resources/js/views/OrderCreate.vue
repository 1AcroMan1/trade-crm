<template>
  <div class="card">
    <h1 class="page-title">Создание нового заказа</h1>
    
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
          :key="index"
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

      <div class="flex justify-end">
        <button
          type="submit"
          class="btn btn-primary"
        >
          Создать заказ
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const form = ref({
  customer: '',
  warehouse_id: '',
  items: [{ product_id: '', count: 1 }]
})

const products = ref([])
const warehouses = ref([])

const fetchProducts = async () => {
  const response = await axios.get('/api/products')
  products.value = response.data.data
}

const fetchWarehouses = async () => {
  const response = await axios.get('/api/warehouses')
  warehouses.value = response.data.data
}

const addItem = () => {
  form.value.items.push({ product_id: '', count: 1 })
}

const removeItem = (index) => {
  if (form.value.items.length > 1) {
    form.value.items.splice(index, 1)
  }
}

const submitForm = async () => {
  try {
    await axios.post('/api/orders', form.value)
    router.push('/orders')
  } catch (error) {
    alert('Ошибка при создании заказа: ' + error.response.data.message)
  }
}

onMounted(() => {
  fetchProducts()
  fetchWarehouses()
})
</script>
