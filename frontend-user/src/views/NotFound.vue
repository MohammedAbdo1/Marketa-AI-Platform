<template>
  <div class="not-found">
    <div class="container text-center">
      <h1>404</h1>
      <h2>Page Not Found</h2>
      <p>The page you're looking for doesn't exist.</p>
      <router-link to="/" class="btn btn-primary">Go Home</router-link>
    </div>
  </div>
</template>

<style scoped>
.not-found {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
}

.not-found h1 {
  font-size: 6rem;
  font-weight: 800;
  color: #2383E2;
  margin-bottom: 1rem;
}

.not-found h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.not-found p {
  color: #718096;
  margin-bottom: 2rem;
}
</style>

