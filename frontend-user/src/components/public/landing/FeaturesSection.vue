<template>
  <section class="features-section">
    <div class="container">
      <div class="section-header text-center">
        <h2>{{ $t('landing.features.title') }}</h2>
        <p>{{ $t('landing.features.subtitle') }}</p>
      </div>
      
      <div class="features-grid">
        <div class="feature-card" v-for="(feature, index) in features" :key="index">
          <div class="feature-icon">
            <i :class="feature.icon"></i>
          </div>
          <h3>{{ feature.title }}</h3>
          <p>{{ feature.description }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const features = ref([
  {
    icon: 'bx bx-brain',
    title: 'ذكاء اصطناعي متقدم',
    description: 'تقنية AI متطورة تفهم السوق العربي'
  },
  {
    icon: 'bx bx-time',
    title: 'توفير الوقت',
    description: 'أنشئ حملات كاملة في دقائق بدلاً من أيام'
  },
  {
    icon: 'bx bx-palette',
    title: 'هوية بصرية متكاملة',
    description: 'إدارة ألوان وشعار علامتك التجارية'
  },
  {
    icon: 'bx bx-calendar',
    title: 'جدولة ذكية',
    description: 'جدول نشر تلقائي لكل المنصات'
  },
  {
    icon: 'bx bx-world',
    title: 'دعم متعدد اللغات',
    description: 'محتوى بالعربية والإنجليزية'
  },
  {
    icon: 'bx bx-line-chart',
    description: 'تقارير وتحليلات مفصلة',
    title: 'تحليلات شاملة'
  }
])
</script>

<style scoped>
.features-section {
  padding: 6rem 0;
  background: white;
}

.section-header {
  margin-bottom: 4rem;
}

.section-header h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.section-header p {
  font-size: 1.25rem;
  color: #718096;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.feature-card {
  padding: 2rem;
  text-align: center;
  border-radius: 12px;
  transition: all 0.3s;
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.feature-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  background: #2383E2;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.feature-card p {
  color: #718096;
}
</style>

