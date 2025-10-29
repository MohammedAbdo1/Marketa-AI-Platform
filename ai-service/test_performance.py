#!/usr/bin/env python3
"""
Performance testing script for Marketa AI Service
Tests the system under load to verify scalability
"""

import asyncio
import aiohttp
import time
import json
import statistics
from typing import List, Dict, Any
import argparse

class PerformanceTester:
    def __init__(self, base_url: str = "http://localhost:8001"):
        self.base_url = base_url
        self.session = None
        self.results = []
    
    async def __aenter__(self):
        self.session = aiohttp.ClientSession()
        return self
    
    async def __aexit__(self, exc_type, exc_val, exc_tb):
        if self.session:
            await self.session.close()
    
    async def test_health_endpoint(self) -> Dict[str, Any]:
        """Test health endpoint performance"""
        start_time = time.time()
        try:
            async with self.session.get(f"{self.base_url}/health") as response:
                duration = time.time() - start_time
                return {
                    "endpoint": "/health",
                    "status_code": response.status,
                    "duration": duration,
                    "success": response.status == 200
                }
        except Exception as e:
            return {
                "endpoint": "/health",
                "status_code": 0,
                "duration": time.time() - start_time,
                "success": False,
                "error": str(e)
            }
    
    async def test_campaign_preview(self, concurrent_requests: int = 10) -> List[Dict[str, Any]]:
        """Test campaign preview endpoint with concurrent requests"""
        test_data = {
            "business_type": "مطعم",
            "product_name": "وجبة شاورما",
            "description": "وجبة شاورما لذيذة ومشبعة",
            "goal": "زيادة المبيعات",
            "target_audience": "الشباب",
            "platforms": ["Instagram", "Facebook"],
            "duration_days": 7,
            "posts_per_week": 5,
            "campaign_id": "test_campaign"
        }
        
        async def single_request():
            start_time = time.time()
            try:
                async with self.session.post(
                    f"{self.base_url}/api/campaign/preview",
                    json=test_data
                ) as response:
                    duration = time.time() - start_time
                    result = await response.json()
                    return {
                        "endpoint": "/api/campaign/preview",
                        "status_code": response.status,
                        "duration": duration,
                        "success": response.status == 200,
                        "task_id": result.get("task_id") if response.status == 200 else None
                    }
            except Exception as e:
                return {
                    "endpoint": "/api/campaign/preview",
                    "status_code": 0,
                    "duration": time.time() - start_time,
                    "success": False,
                    "error": str(e)
                }
        
        # Run concurrent requests
        tasks = [single_request() for _ in range(concurrent_requests)]
        results = await asyncio.gather(*tasks)
        return results
    
    async def test_task_status(self, task_id: str) -> Dict[str, Any]:
        """Test task status endpoint"""
        start_time = time.time()
        try:
            async with self.session.get(f"{self.base_url}/api/task/status/{task_id}") as response:
                duration = time.time() - start_time
                result = await response.json()
                return {
                    "endpoint": f"/api/task/status/{task_id}",
                    "status_code": response.status,
                    "duration": duration,
                    "success": response.status == 200,
                    "task_status": result.get("status") if response.status == 200 else None
                }
        except Exception as e:
            return {
                "endpoint": f"/api/task/status/{task_id}",
                "status_code": 0,
                "duration": time.time() - start_time,
                "success": False,
                "error": str(e)
            }
    
    async def test_rate_limiting(self, user_id: str = "test_user", requests_count: int = 15) -> List[Dict[str, Any]]:
        """Test rate limiting by making many requests"""
        results = []
        
        for i in range(requests_count):
            start_time = time.time()
            try:
                headers = {"X-User-ID": user_id}
                async with self.session.get(
                    f"{self.base_url}/api/rate-limit/stats/{user_id}",
                    headers=headers
                ) as response:
                    duration = time.time() - start_time
                    result = await response.json()
                    results.append({
                        "request_number": i + 1,
                        "status_code": response.status,
                        "duration": duration,
                        "rate_limited": response.status == 429,
                        "remaining": result.get("remaining") if response.status == 200 else None
                    })
            except Exception as e:
                results.append({
                    "request_number": i + 1,
                    "status_code": 0,
                    "duration": time.time() - start_time,
                    "rate_limited": False,
                    "error": str(e)
                })
            
            # Small delay between requests
            await asyncio.sleep(0.1)
        
        return results
    
    async def test_websocket_connection(self) -> Dict[str, Any]:
        """Test WebSocket connection (simplified test)"""
        start_time = time.time()
        try:
            # This is a simplified test - in real implementation you'd use socketio client
            async with self.session.get(f"{self.base_url}/health/workers") as response:
                duration = time.time() - start_time
                return {
                    "endpoint": "websocket_health",
                    "status_code": response.status,
                    "duration": duration,
                    "success": response.status == 200
                }
        except Exception as e:
            return {
                "endpoint": "websocket_health",
                "status_code": 0,
                "duration": time.time() - start_time,
                "success": False,
                "error": str(e)
            }
    
    def analyze_results(self, results: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Analyze test results and generate statistics"""
        if not results:
            return {"error": "No results to analyze"}
        
        successful_requests = [r for r in results if r.get("success", False)]
        failed_requests = [r for r in results if not r.get("success", False)]
        
        durations = [r.get("duration", 0) for r in successful_requests if r.get("duration")]
        
        analysis = {
            "total_requests": len(results),
            "successful_requests": len(successful_requests),
            "failed_requests": len(failed_requests),
            "success_rate": len(successful_requests) / len(results) * 100 if results else 0,
            "average_duration": statistics.mean(durations) if durations else 0,
            "min_duration": min(durations) if durations else 0,
            "max_duration": max(durations) if durations else 0,
            "median_duration": statistics.median(durations) if durations else 0,
            "p95_duration": statistics.quantiles(durations, n=20)[18] if len(durations) >= 20 else max(durations) if durations else 0,
            "p99_duration": statistics.quantiles(durations, n=100)[98] if len(durations) >= 100 else max(durations) if durations else 0
        }
        
        return analysis
    
    async def run_comprehensive_test(self, concurrent_users: int = 50, requests_per_user: int = 10):
        """Run comprehensive performance test"""
        print(f"🚀 Starting performance test with {concurrent_users} concurrent users, {requests_per_user} requests each")
        
        start_time = time.time()
        
        # Test 1: Health endpoint
        print("📊 Testing health endpoint...")
        health_result = await self.test_health_endpoint()
        print(f"   Health check: {health_result['duration']:.3f}s")
        
        # Test 2: Campaign preview with concurrent requests
        print("📊 Testing campaign preview endpoint...")
        preview_results = await self.test_campaign_preview(concurrent_users)
        preview_analysis = self.analyze_results(preview_results)
        print(f"   Preview requests: {preview_analysis['success_rate']:.1f}% success, avg {preview_analysis['average_duration']:.3f}s")
        
        # Test 3: Rate limiting
        print("📊 Testing rate limiting...")
        rate_limit_results = await self.test_rate_limiting("test_user", 15)
        rate_limited_count = len([r for r in rate_limit_results if r.get("rate_limited", False)])
        print(f"   Rate limiting: {rate_limited_count} requests rate limited")
        
        # Test 4: WebSocket health
        print("📊 Testing WebSocket health...")
        websocket_result = await self.test_websocket_connection()
        print(f"   WebSocket health: {websocket_result['duration']:.3f}s")
        
        total_duration = time.time() - start_time
        
        # Generate final report
        report = {
            "test_summary": {
                "total_duration": total_duration,
                "concurrent_users": concurrent_users,
                "requests_per_user": requests_per_user,
                "total_requests": len(preview_results)
            },
            "health_endpoint": health_result,
            "campaign_preview": preview_analysis,
            "rate_limiting": {
                "total_requests": len(rate_limit_results),
                "rate_limited": rate_limited_count,
                "rate_limit_percentage": rate_limited_count / len(rate_limit_results) * 100 if rate_limit_results else 0
            },
            "websocket_health": websocket_result,
            "performance_grade": self.calculate_performance_grade(preview_analysis)
        }
        
        return report
    
    def calculate_performance_grade(self, analysis: Dict[str, Any]) -> str:
        """Calculate performance grade based on metrics"""
        success_rate = analysis.get("success_rate", 0)
        avg_duration = analysis.get("average_duration", 0)
        
        if success_rate >= 95 and avg_duration <= 2.0:
            return "A+ (Excellent)"
        elif success_rate >= 90 and avg_duration <= 3.0:
            return "A (Very Good)"
        elif success_rate >= 80 and avg_duration <= 5.0:
            return "B (Good)"
        elif success_rate >= 70 and avg_duration <= 10.0:
            return "C (Acceptable)"
        else:
            return "D (Needs Improvement)"

async def main():
    parser = argparse.ArgumentParser(description="Performance test for Marketa AI Service")
    parser.add_argument("--url", default="http://localhost:8001", help="Base URL of the service")
    parser.add_argument("--users", type=int, default=50, help="Number of concurrent users")
    parser.add_argument("--requests", type=int, default=10, help="Requests per user")
    parser.add_argument("--output", help="Output file for results")
    
    args = parser.parse_args()
    
    async with PerformanceTester(args.url) as tester:
        print("🧪 Marketa AI Service Performance Test")
        print("=" * 50)
        
        report = await tester.run_comprehensive_test(args.users, args.requests)
        
        print("\n📈 Performance Report")
        print("=" * 50)
        print(f"Total Duration: {report['test_summary']['total_duration']:.2f}s")
        print(f"Success Rate: {report['campaign_preview']['success_rate']:.1f}%")
        print(f"Average Response Time: {report['campaign_preview']['average_duration']:.3f}s")
        print(f"P95 Response Time: {report['campaign_preview']['p95_duration']:.3f}s")
        print(f"Performance Grade: {report['performance_grade']}")
        
        if args.output:
            with open(args.output, 'w') as f:
                json.dump(report, f, indent=2)
            print(f"\n📄 Detailed report saved to {args.output}")

if __name__ == "__main__":
    asyncio.run(main())
