<template>
  <div class="wizard-step">
    <div class="step-header mb-4">
      <h3>{{ $t('campaigns.wizard.step1.title') }}</h3>
      <p class="text-muted">{{ $t('campaigns.wizard.step1.subtitle') }}</p>
    </div>

    <form @submit.prevent="handleNext">
      <div class="row">
        <!-- Business Type -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step1.businessType') }} *</label>
          <select 
            v-model="localData.business_type" 
            class="form-select"
            :class="{ 'is-invalid': errors.business_type }"
            required
          >
            <option value="">{{ $t('common.selectOption') }}</option>
            <option value="restaurant">{{ $t('campaigns.businessTypes.restaurant') }}</option>
            <option value="retail">{{ $t('campaigns.businessTypes.retail') }}</option>
            <option value="services">{{ $t('campaigns.businessTypes.services') }}</option>
            <option value="healthcare">{{ $t('campaigns.businessTypes.healthcare') }}</option>
            <option value="education">{{ $t('campaigns.businessTypes.education') }}</option>
            <option value="technology">{{ $t('campaigns.businessTypes.technology') }}</option>
            <option value="fashion">{{ $t('campaigns.businessTypes.fashion') }}</option>
            <option value="beauty">{{ $t('campaigns.businessTypes.beauty') }}</option>
            <option value="fitness">{{ $t('campaigns.businessTypes.fitness') }}</option>
            <option value="real_estate">{{ $t('campaigns.businessTypes.realEstate') }}</option>
            <option value="automotive">{{ $t('campaigns.businessTypes.automotive') }}</option>
            <option value="travel">{{ $t('campaigns.businessTypes.travel') }}</option>
            <option value="food">{{ $t('campaigns.businessTypes.food') }}</option>
            <option value="entertainment">{{ $t('campaigns.businessTypes.entertainment') }}</option>
            <option value="other">{{ $t('campaigns.businessTypes.other') }}</option>
          </select>
          <div v-if="errors.business_type" class="invalid-feedback">
            {{ errors.business_type }}
          </div>
        </div>

        <!-- Product/Service Name -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step1.productName') }} *</label>
          <input 
            v-model="localData.product_name"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.product_name }"
            :placeholder="$t('campaigns.wizard.step1.productNamePlaceholder')"
            required
          />
          <div v-if="errors.product_name" class="invalid-feedback">
            {{ errors.product_name }}
          </div>
        </div>

        <!-- Description -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step1.description') }} *</label>
          <textarea 
            v-model="localData.description"
            class="form-control"
            :class="{ 'is-invalid': errors.description }"
            rows="4"
            :placeholder="$t('campaigns.wizard.step1.descriptionPlaceholder')"
            required
          ></textarea>
          <div class="form-text">{{ $t('campaigns.wizard.step1.descriptionHelp') }}</div>
          <div v-if="errors.description" class="invalid-feedback">
            {{ errors.description }}
          </div>
        </div>
      </div>

      <!-- Step Actions -->
      <div class="step-actions mt-4">
        <div class="d-flex justify-content-end">
          <button 
            type="submit" 
            class="btn btn-primary"
            :disabled="!isValid"
          >
            {{ $t('common.next') }} <i class="bx bx-right-arrow-alt ms-1"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  wizardData: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:wizardData', 'next', 'back'])

// Local data
const localData = ref({ ...props.wizardData })
const errors = ref({})

// Computed
const isValid = computed(() => {
  return localData.value.business_type && 
         localData.value.product_name && 
         localData.value.description
})

// Watch for changes
watch(localData, (newData) => {
  emit('update:wizardData', newData)
}, { deep: true })

// Methods
const validateForm = () => {
  errors.value = {}
  
  if (!localData.value.business_type) {
    errors.value.business_type = 'Business type is required'
  }
  
  if (!localData.value.product_name?.trim()) {
    errors.value.product_name = 'Product name is required'
  }
  
  if (!localData.value.description?.trim()) {
    errors.value.description = 'Description is required'
  } else if (localData.value.description.length < 20) {
    errors.value.description = 'Description must be at least 20 characters'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleNext = () => {
  if (validateForm()) {
    emit('next')
  }
}
</script>

<style scoped>
.wizard-step {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-header {
  text-align: center;
  margin-bottom: 2rem;
}

.step-header h3 {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
}

.form-control, .form-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.75rem 1rem;
  transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.step-actions {
  border-top: 1px solid #e9ecef;
  padding-top: 1.5rem;
}

.btn {
  border-radius: 8px;
  padding: 0.75rem 2rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3, #004085);
  transform: translateY(-1px);
}

.form-text {
  font-size: 0.875rem;
  color: #6c757d;
  margin-top: 0.25rem;
}

.invalid-feedback {
  display: block;
  font-size: 0.875rem;
  color: #dc3545;
  margin-top: 0.25rem;
}

.is-invalid {
  border-color: #dc3545;
}

@media (max-width: 768px) {
  .step-header {
    text-align: left;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
