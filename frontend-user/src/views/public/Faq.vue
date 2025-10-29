<template>
  <div class="faq-page">
    <div class="container">
      <section class="faq-hero">
        <h1>{{ $t('landing.faq.title') }}</h1>
        <p>{{ $t('landing.faq.subtitle') }}</p>
      </section>
      
      <section class="faq-content">
        <div class="accordion" v-for="(faq, index) in faqs" :key="faq.id">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button 
                class="accordion-button" 
                :class="{ collapsed: activeIndex !== index }"
                @click="toggleAccordion(index)"
              >
                {{ faq.question_ar }}
              </button>
            </h2>
            <div 
              class="accordion-collapse" 
              :class="{ show: activeIndex === index }"
            >
              <div class="accordion-body">
                {{ faq.answer_ar }}
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/axios'

const faqs = ref([])
const activeIndex = ref(0)

const toggleAccordion = (index) => {
  activeIndex.value = activeIndex.value === index ? null : index
}

onMounted(async () => {
  try {
    const response = await axios.get('/faqs')
    const grouped = response.data.data
    faqs.value = Object.values(grouped).flat()
  } catch (error) {
    console.error('Error loading FAQs:', error)
  }
})
</script>

<style scoped>
.faq-page {
  padding-top: 100px;
  min-height: 70vh;
}

.faq-hero {
  text-align: center;
  padding: 4rem 0 3rem;
}

.faq-hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.faq-content {
  max-width: 800px;
  margin: 0 auto;
  padding-bottom: 4rem;
}

.accordion {
  margin-bottom: 1rem;
}

.accordion-item {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.accordion-button {
  width: 100%;
  padding: 1.5rem;
  background: white;
  border: none;
  text-align: start;
  font-size: 1.125rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.accordion-button:not(.collapsed) {
  background: #f8f9fa;
  color: var(--primary-color);
}

.accordion-collapse {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
}

.accordion-collapse.show {
  max-height: 500px;
}

.accordion-body {
  padding: 1.5rem;
  color: #718096;
  line-height: 1.8;
}
</style>

