<template>
    <div class="booking-form">
        <div class="container">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Бронирование услуг</h1>
                    </div>
                </div>
            </div>
        </section>

      <!-- Выбор услуги -->
        <div class="step" v-if="currentStep === 1">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Выберите услугу</h3>
                </div>
                <div class="card-body">
                    <div class="services-grid">
                    <div
                        v-for="service in services"
                        :key="service.id"
                        class="service-card"
                        :class="{ 'selected': selectedService?.id === service.id }"
                        @click="selectService(service)">
                        <h3>{{ service.name }}</h3>
                        <p>{{ service.description }}</p>
                        <div class="service-info">
                        <span class="duration"> {{ service.duration_minutes }} мин.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button
          class="btn-primary"
          @click="nextStep"
          :disabled="!selectedService"
        >
          Выбрать дату
        </button>
    </div>

    <!-- Выбор даты и времени -->
    <div class="step" v-if="currentStep === 2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Выберите дату и время</h3>
            </div>

            <div class="card-body">

            <div class="week-navigation">
                <button @click="previousWeek" class="btn-secondary">Предыдущая</button>
                <span class="current-week">{{ currentWeekRange }}</span>
                <button @click="nextWeek" class="btn-secondary">Следующая</button>
            </div>

            <div class="week-calendar">
                <div
                    v-for="day in weekDays"
                    :key="day.date"
                    class="day-card"
                    :class="{
                    'selected': selectedDate === day.date,
                    'disabled': !day.isAvailable,
                    'sunday': day.isSunday
                    }"
                    @click="selectDate(day)"
                >
                    <div class="day-header">
                        <div class="day-name">{{ day.dayName }}</div>
                        <div class="day-number">{{ day.dayNumber }}</div>
                    </div>
                    <div class="day-status">
                        <span v-if="day.isSunday">Выходной</span>
                        <span v-else-if="!day.isAvailable">Недоступно</span>
                        <span v-else class="available">Доступно</span>
                    </div>
                </div>
            </div>

            <!-- Доступные слоты -->
            <div v-if="selectedDate && availableSlots.length > 0" class="time-slots">
                <h3>Доступное время на {{ selectedDateFormatted }}</h3>
                <div class="slots-grid">
                    <button
                        v-for="slot in availableSlots"
                        :key="slot.start_time"
                        class="time-slot"
                        :class="{ 'selected': selectedSlot?.start_time === slot.start_time }"
                        @click="selectTimeSlot(slot)"
                        >
                        <span class="time">{{ slot.start_time }} - {{ slot.end_time }}</span>
                    </button>
                </div>
            </div>

            <div v-else-if="selectedDate" class="no-slots">
                <p>На выбранную дату нет доступных слотов</p>
            </div>

            <div class="navigation-buttons">
                <button @click="prevStep" class="btn-secondary">Назад</button>
                <button
                    @click="nextStep"
                    class="btn-primary"
                    :disabled="!selectedSlot"
                >
                    Завершить бронирование
                </button>
            </div>
            </div>
        </div>
      </div>

    <!-- Форма бронирования -->
    <div class="step" v-if="currentStep === 3">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Данные для бронирования</h3>
            </div>

            <div class="card-body">
                <div class="booking-summary">
                    <p><strong>Услуга:</strong> {{ selectedService.name }}</p>
                    <p><strong>Дата:</strong> {{ selectedDateFormatted }}</p>
                    <p><strong>Время:</strong> {{ selectedSlot.start_time }} - {{ selectedSlot.end_time }}</p>
                    <p><strong>Продолжительность:</strong> {{ selectedService.duration_minutes }} ( + 30 ) минут </p>
                </div>

                <form @submit.prevent="submitBooking" class="booking-form-details">
                    <div class="form-group">
                        <label for="customerName">Ваше имя *</label>
                        <input
                        id="customerName"
                        v-model="bookingForm.customer_name"
                        type="text"
                        required
                        placeholder="Введите ваше имя"
                        >
                    </div>

                    <div class="form-group">
                        <label for="customerEmail">Email *</label>
                        <input
                        id="customerEmail"
                        v-model="bookingForm.customer_email"
                        type="email"
                        required
                        placeholder="Введите ваш email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="customerPhone">Телефон *</label>
                        <input
                        id="customerPhone"
                        v-model="bookingForm.customer_phone"
                        type="tel"
                        required
                        placeholder="Введите ваш телефон"
                        >
                    </div>

                    <div class="form-actions">
                        <button type="button" @click="prevStep" class="btn-secondary">Назад</button>
                        <button type="submit" class="btn-primary" :disabled="loading">
                        {{ loading ? 'Бронируем...' : 'Забронировать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

      <!-- Шаг 4: Подтверждение -->
      <div class="step" v-if="currentStep === 4">
        <div class="success-message">
          <h2>Бронирование успешно создано!</h2>
          <button @click="resetForm" class="btn-primary">Еще</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'BookingForm',
  setup() {
    const currentStep = ref(1)
    const services = ref([])
    const selectedService = ref(null)
    const currentWeekStart = ref(new Date())
    const selectedDate = ref(null)
    const selectedSlot = ref(null)
    const availableSlots = ref([])
    const loading = ref(false)
    const bookingForm = ref({
      customer_name: '',
      customer_email: '',
      customer_phone: ''
    })

    const weekDays = computed(() => {
      const days = []
      const start = new Date(currentWeekStart.value)

      for (let i = 0; i < 7; i++) {
        const date = new Date(start)
        date.setDate(start.getDate() + i)

        const isSunday = date.getDay() === 0
        const isAvailable = !isSunday &&
                           date >= new Date(new Date().setHours(0, 0, 0, 0)) &&
                           date.getDay() !== 0

        days.push({
          date: date.toISOString().split('T')[0],
          dayName: date.toLocaleDateString('ru-RU', { weekday: 'short' }),
          dayNumber: date.getDate(),
          month: date.toLocaleDateString('ru-RU', { month: 'short' }),
          isSunday,
          isAvailable
        })
      }
      return days
    })

    const currentWeekRange = computed(() => {
      const start = new Date(currentWeekStart.value)
      const end = new Date(start)
      end.setDate(start.getDate() + 6)

      return `${start.toLocaleDateString('ru-RU')} - ${end.toLocaleDateString('ru-RU')}`
    })

    const selectedDateFormatted = computed(() => {
      if (!selectedDate.value) return ''
      return new Date(selectedDate.value).toLocaleDateString('ru-RU', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    })

    const fetchServices = async () => {
      try {
        const response = await axios.get('/api/services')
        services.value = response.data
      } catch (error) {
        console.error('Ошибка загрузки услуг:', error)
        alert('Не удалось загрузить список услуг')
      }
    }

    const selectService = (service) => {
      selectedService.value = service
    }

    const selectDate = async (day) => {
      if (!day.isAvailable || day.isSunday) return

      selectedDate.value = day.date
      selectedSlot.value = null

      try {
        const response = await axios.get(`/api/services/${selectedService.value.id}/available-slots`, {
          params: { date: day.date }
        })
        availableSlots.value = response.data
      } catch (error) {
        console.error('Ошибка загрузки слотов:', error)
        alert('Не удалось загрузить доступное время')
      }
    }

    const selectTimeSlot = (slot) => {
      selectedSlot.value = slot
    }

    const previousWeek = () => {
      const newDate = new Date(currentWeekStart.value)
      newDate.setDate(newDate.getDate() - 7)
      currentWeekStart.value = newDate
    }

    const nextWeek = () => {
      const newDate = new Date(currentWeekStart.value)
      newDate.setDate(newDate.getDate() + 7)
      currentWeekStart.value = newDate
    }

    const nextStep = () => {
      currentStep.value++
    }

    const prevStep = () => {
      currentStep.value--
    }

    const submitBooking = async () => {
      if (!selectedService.value || !selectedDate.value || !selectedSlot.value) {
        alert('Пожалуйста, заполните все поля')
        return
      }

      loading.value = true

      try {
        await axios.post('/api/bookings', {
          service_id: selectedService.value.id,
          customer_name: bookingForm.value.customer_name,
          customer_email: bookingForm.value.customer_email,
          customer_phone: bookingForm.value.customer_phone,
          booking_date: selectedDate.value,
          start_time: selectedSlot.value.start_time
        })

        currentStep.value = 4
      } catch (error) {
        console.error('Ошибка бронирования:', error)
        if (error.response?.data?.message) {
          alert(error.response.data.message)
        } else {
          alert('Произошла ошибка при бронировании. Пожалуйста, попробуйте еще раз.')
        }
      } finally {
        loading.value = false
      }
    }

    const resetForm = () => {
      currentStep.value = 1
      selectedService.value = null
      selectedDate.value = null
      selectedSlot.value = null
      availableSlots.value = []
      bookingForm.value = {
        customer_name: '',
        customer_email: '',
        customer_phone: ''
      }

      currentWeekStart.value = new Date()
    }

    onMounted(() => {
      fetchServices()

      const today = new Date()
      const dayOfWeek = today.getDay()
      const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1)
      currentWeekStart.value = new Date(today.setDate(diff))
    })

    return {
      currentStep,
      services,
      selectedService,
      currentWeekStart,
      selectedDate,
      selectedSlot,
      availableSlots,
      loading,
      bookingForm,
      weekDays,
      currentWeekRange,
      selectedDateFormatted,
      selectService,
      selectDate,
      selectTimeSlot,
      previousWeek,
      nextWeek,
      nextStep,
      prevStep,
      submitBooking,
      resetForm
    }
  }
}
</script>

<style scoped>
.booking-form {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.container {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.step h2 {
  color: #2c5aa0;
  margin-bottom: 20px;
}

/* Стили для услуг */
.services-grid {
  display: flex;
  flex-direction: column;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.service-card {
  border: 2px solid #e0e0e0;
  border-radius: 4px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.service-card:hover {
  border-color: #2c5aa0;
  transform: translateY(-2px);
}

.service-card.selected {
  border-color: #2c5aa0;
  background-color: #f0f7ff;
}

.service-info {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
  font-size: 14px;
}

.week-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.current-week {
  font-weight: bold;
  color: #333;
}

.week-calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.day-card {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 5px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.day-card:hover:not(.disabled) {
  border-color: #2c5aa0;
}

.day-card.selected {
  border-color: #2c5aa0;
  background-color: #f0f7ff;
}

.day-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background-color: #f5f5f5;
}

.day-card.sunday {
  background-color: #fff0f0;
}

.day-header {
  margin-bottom: 8px;
}

.day-name {
  font-weight: bold;
  text-transform: capitalize;
}

.day-number {
  font-size: 18px;
  font-weight: bold;
}

.day-status {
  font-size: 12px;
}

.available {
  color: #28a745;
}

.time-slots {
  margin: 30px 0;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 10px;
  margin-top: 15px;
}

.time-slot {
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  padding: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.time-slot:hover {
  border-color: #2c5aa0;
}

.time-slot.selected {
  border-color: #2c5aa0;
  background-color: #f0f7ff;
}

.booking-summary {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  font-size: 16px;
}

.form-group input:focus {
  border-color: #2c5aa0;
  outline: none;
}

.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 30px;
}

.btn-primary {
  background: #2c5aa0;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #1e3d6f;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6c757d;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.3s ease;
}

.btn-secondary:hover {
  background: #545b62;
}

.no-slots {
  text-align: center;
  padding: 40px;
  color: #666;
}

.success-message {
  text-align: center;
  padding: 40px;
}

.success-message h2 {
  color: #28a745;
  margin-bottom: 15px;
}

.navigation-buttons button:first-child {
    margin-right: 20px;
}

@media (max-width: 768px) {
  .week-calendar {
    grid-template-columns: repeat(2, 1fr);
  }

  .services-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
