<template>
  <section class="pricing-section">
    <div class="container">
      <div class="section-header text-center">
        <h2>{{ $t('landing.pricing.title') }}</h2>
        <p>{{ $t('landing.pricing.subtitle') }}</p>
      </div>
      
      <div class="pricing-grid">
        <div 
          class="pricing-card" 
          v-for="plan in plans" 
          :key="plan.id"
          :class="{ popular: plan.is_popular }"
        >
          <div class="popular-badge" v-if="plan.is_popular">
            {{ $t('landing.pricing.popular') }}
          </div>
          
          <h3>{{ plan.name_ar }}</h3>
          <div class="price">
            <span class="amount">{{ plan.price_monthly }}</span>
            <span class="currency">ريال</span>
            <span class="period">/{{ $t('landing.pricing.monthly') }}</span>
          </div>
          
          <p class="plan-description">{{ plan.description_ar }}</p>
          
          <ul class="features-list">
            <li v-for="(feature, index) in parseFeatures(plan.features)" :key="index">
              <i class="bx bx-check"></i>
              {{ feature }}
            </li>
          </ul>
          
          <router-link 
            to="/auth/register" 
            class="btn" 
            :class="plan.is_popular ? 'btn-primary' : 'btn-outline'"
          >
            {{ $t('landing.pricing.choose_plan') }}
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/axios'

const plans = ref([])

const parseFeatures = (featuresJson) => {
  try {
    return JSON.parse(featuresJson || '[]')
  } catch {
    return []
  }
}

onMounted(async () => {
  try {
    const response = await axios.get('/plans')
    plans.value = response.data.data
  } catch (error) {
    console.error('Error loading plans:', error)
  }
})
</script>

<style scoped>
.pricing-section {
  padding: 6rem 0;
  background: white;
}

.pricing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.pricing-card {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: 16px;
  padding: 2.5rem 2rem;
  text-align: center;
  position: relative;
  transition: all 0.3s;
}

.pricing-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.1);
}

.pricing-card.popular {
  border-color: #2383E2;
  box-shadow: 0 8px 24px rgba(35, 131, 226, 0.2);
}

.popular-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: #2383E2;
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.pricing-card h3 {
  font-size: 1.75rem;
  margin-bottom: 1rem;
}

.price {
  margin-bottom: 1.5rem;
}

.price .amount {
  font-size: 3rem;
  font-weight: 700;
  color: #2383E2;
}

.price .currency {
  font-size: 1.25rem;
  color: #718096;
}

.price .period {
  font-size: 1rem;
  color: #718096;
}

.plan-description {
  color: #718096;
  margin-bottom: 2rem;
}

.features-list {
  list-style: none;
  padding: 0;
  margin: 2rem 0;
  text-align: start;
}

.features-list li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f1f1;
}

.features-list li:last-child {
  border-bottom: none;
}

.features-list i {
  color: #2383E2;
  font-size: 1.5rem;
}

.btn {
  width: 100%;
  margin-top: 1.5rem;
}
</style>

