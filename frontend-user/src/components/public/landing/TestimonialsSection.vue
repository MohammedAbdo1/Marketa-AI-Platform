<template>
  <section class="testimonials-section">
    <div class="container">
      <div class="section-header text-center">
        <h2>{{ $t('landing.testimonials.title') }}</h2>
        <p>{{ $t('landing.testimonials.subtitle') }}</p>
      </div>
      
      <div class="testimonials-grid">
        <div class="testimonial-card" v-for="testimonial in testimonials" :key="testimonial.id">
          <div class="stars">
            <i class="bx bxs-star" v-for="i in testimonial.rating" :key="i"></i>
          </div>
          <p class="testimonial-content">{{ testimonial.content }}</p>
          <div class="testimonial-author">
            <div class="author-avatar">{{ testimonial.name.charAt(0) }}</div>
            <div>
              <h4>{{ testimonial.name }}</h4>
              <p>{{ testimonial.position }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/axios'

const testimonials = ref([])

onMounted(async () => {
  try {
    const response = await axios.get('/testimonials?featured=true')
    testimonials.value = response.data.data.map(t => ({
      id: t.id,
      name: t.name_ar,
      position: t.position_ar,
      content: t.content_ar,
      rating: t.rating
    }))
  } catch (error) {
    console.error('Error loading testimonials:', error)
  }
})
</script>

<style scoped>
.testimonials-section {
  padding: 6rem 0;
  background: #f8f9fa;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.testimonial-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stars {
  color: #ffc107;
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.testimonial-content {
  margin-bottom: 1.5rem;
  font-style: italic;
  color: #495057;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.author-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #2383E2;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.25rem;
}

.testimonial-author h4 {
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.testimonial-author p {
  font-size: 0.875rem;
  color: #718096;
  margin: 0;
}
</style>

