<template>
  <footer class="public-footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>{{ $t('app.name') }}</h3>
          <p>{{ $t('app.tagline') }}</p>
        </div>
        
        <div class="footer-section">
          <h4>{{ $t('footer.about') }}</h4>
          <ul>
            <li><router-link to="/about">{{ $t('nav.about') }}</router-link></li>
            <li><router-link to="/pricing">{{ $t('footer.pricing') }}</router-link></li>
            <li><router-link to="/faq">{{ $t('nav.faq') }}</router-link></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h4>{{ $t('footer.contact') }}</h4>
          <ul>
            <li><a href="mailto:info@marketa.ai">info@marketa.ai</a></li>
            <li><a href="#">{{ $t('footer.privacy') }}</a></li>
            <li><a href="#">{{ $t('footer.terms') }}</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h4>{{ $t('common.language') }}</h4>
          <button @click="toggleLanguage" class="btn-language">
            <i class="bx bx-globe"></i>
            {{ currentLocale === 'ar' ? $t('common.english') : $t('common.arabic') }}
          </button>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2025 {{ $t('app.name') }}. {{ $t('footer.all_rights_reserved') }}</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { setLocale } from '@/i18n'

const { locale: currentLocale } = useI18n()

const toggleLanguage = () => {
  const newLocale = currentLocale.value === 'ar' ? 'en' : 'ar'
  setLocale(newLocale)
}
</script>

<style scoped>
.public-footer {
  background: #ffffff;
    color: rgb(51, 51, 51);
  padding: 4rem 0 2rem;
  margin-top: 4rem;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-section h3,
.footer-section h4 {
  margin-bottom: 1rem;
  font-weight: 600;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section li {
  margin-bottom: 0.5rem;
}

.footer-section a {
  color: rgba(32, 32, 32, 0.7);
  text-decoration: none;
  transition: color 0.3s;
}



.btn-language {
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-language:hover {
  background: rgba(255, 255, 255, 0.2);
}

.footer-bottom {
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}
</style>

