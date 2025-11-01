#!/bin/bash
# Test AI Service from Laravel container

echo "========================================="
echo "Testing AI Service from Laravel"
echo "========================================="

echo -e "\n[TEST 1] Health Check"
docker exec marketa_backend curl -s http://api:8001/health

echo -e "\n\n[TEST 2] Campaign Preview"
docker exec marketa_backend curl -s -X POST http://api:8001/api/campaign/preview \
  -H "Content-Type: application/json" \
  -d '{"business_type":"restaurant","product_name":"Test Restaurant","description":"Beautiful modern restaurant","campaign_goal":"awareness","target_audience":{"age":"25-45"},"platforms":["instagram"],"duration_weeks":2,"posts_per_week":3,"mode":"quick"}'

echo -e "\n\n========================================="
echo "Tests Complete!"
echo "========================================="

