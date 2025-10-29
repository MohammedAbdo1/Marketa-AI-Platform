<template>
  <header class="public-header" :class="{ scrolled: isScrolled }">
    <div class="container">
      <nav class="navbar">
        <router-link to="/" class="logo">
          <img src="@/assets/logo.png" alt="Logo" class="logo-img">
        </router-link>
        
        <div class="nav-menu" :class="{ active: menuOpen }">
          <router-link to="/" class="nav-link">{{ $t('nav.home') }}</router-link>
          <router-link to="/about" class="nav-link">{{ $t('nav.about') }}</router-link>
          <router-link to="/pricing" class="nav-link">{{ $t('nav.pricing') }}</router-link>
          <router-link to="/faq" class="nav-link">{{ $t('nav.faq') }}</router-link>
        </div>
        
        <div class="nav-actions">
          <button @click="toggleLanguage" class="btn-language">
            {{ currentLocale === 'ar' ? 'EN' : 'عربي' }}
          </button>
          
          <router-link to="/auth/login" class="btn btn-outline d-none d-md-block">
            {{ $t('nav.login') }}
          </router-link>
          
          <router-link to="/auth/register" class="btn btn-primary">
            {{ $t('nav.start_free') }}
          </router-link>
          
          <button class="btn-menu" @click="menuOpen = !menuOpen">
            <i class="bx" :class="menuOpen ? 'bx-x' : 'bx-menu'"></i>
          </button>
        </div>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale } from '@/i18n'

const { locale: currentLocale } = useI18n()
const isScrolled = ref(false)
const menuOpen = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

const toggleLanguage = () => {
  const newLocale = currentLocale.value === 'ar' ? 'en' : 'ar'
  setLocale(newLocale)
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.public-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: transparent;
  transition: all 0.3s ease;
  padding: 1rem 0;
}

.public-header.scrolled {
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  text-decoration: none;
  color: inherit;
  font-size: 1.5rem;
  font-weight: bold;
}

.nav-menu {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.nav-link {
  text-decoration: none;
  color: inherit;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-link:hover {
  color: #2383E2;
}

.nav-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn {
  padding: 0.5rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
}

.btn-outline {
  background: transparent;
  border: 2px solid currentColor;
  color: inherit;
}

.btn-primary {
  background: #2383E2;
  color: white;
}

.btn-primary:hover {
  background: #1a6bbf;
}

.btn-language {
  padding: 0.5rem 1rem;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  background: white;
  cursor: pointer;
  font-weight: 500;
}

.btn-menu {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

/* Mobile */
@media (max-width: 768px) {
  .nav-menu {
    display: none;
  }
  
  .btn-menu {
    display: block;
  }
  
  .nav-menu.active {
    display: flex;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    padding: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }
}
</style>

