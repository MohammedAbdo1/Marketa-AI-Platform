import socketio
from fastapi import FastAPI
from config import settings
import asyncio
from typing import Dict, Any

# Create Socket.IO server with explicit CORS origins
sio = socketio.AsyncServer(
    cors_allowed_origins=["*"],
    logger=True,
    engineio_logger=True,
    always_connect=True
)

# Store for active connections
active_connections: Dict[str, Any] = {}

@sio.event
async def connect(sid, environ, auth):
    """Handle client connection"""
    print(f"Client {sid} connected")
    active_connections[sid] = {
        "connected_at": asyncio.get_event_loop().time(),
        "user_id": None,
        "last_activity": asyncio.get_event_loop().time()
    }
    
    # Send welcome message
    await sio.emit('connected', {
        'message': 'Connected to Marketa AI Service',
        'sid': sid
    }, room=sid)

@sio.event
async def disconnect(sid):
    """Handle client disconnection"""
    print(f"Client {sid} disconnected")
    if sid in active_connections:
        del active_connections[sid]

@sio.event
async def join_user(sid, data):
    """Join user to their personal room for task updates"""
    user_id = data.get('user_id')
    if user_id:
        await sio.enter_room(sid, f"user_{user_id}")
        active_connections[sid]["user_id"] = user_id
        print(f"Client {sid} joined user room: user_{user_id}")

@sio.event
async def leave_user(sid, data):
    """Leave user room"""
    user_id = data.get('user_id')
    if user_id:
        await sio.leave_room(sid, f"user_{user_id}")
        active_connections[sid]["user_id"] = None
        print(f"Client {sid} left user room: user_{user_id}")

@sio.event
async def subscribe_task(sid, data):
    """Subscribe to task updates"""
    task_id = data.get('task_id')
    if task_id:
        await sio.enter_room(sid, f"task_{task_id}")
        print(f"Client {sid} subscribed to task: {task_id}")

@sio.event
async def unsubscribe_task(sid, data):
    """Unsubscribe from task updates"""
    task_id = data.get('task_id')
    if task_id:
        await sio.leave_room(sid, f"task_{task_id}")
        print(f"Client {sid} unsubscribed from task: {task_id}")

@sio.event
async def ping(sid, data):
    """Handle ping for connection health"""
    active_connections[sid]["last_activity"] = asyncio.get_event_loop().time()
    await sio.emit('pong', {'timestamp': asyncio.get_event_loop().time()}, room=sid)

# Task update functions
async def send_task_update(task_id: str, status: str, progress: int = 0, message: str = "", result: Any = None):
    """Send task update to subscribed clients"""
    update_data = {
        "task_id": task_id,
        "status": status,
        "progress": progress,
        "message": message,
        "timestamp": asyncio.get_event_loop().time()
    }
    
    if result:
        update_data["result"] = result
    
    await sio.emit('task_update', update_data, room=f"task_{task_id}")

async def send_task_completion(task_id: str, result: Any):
    """Send task completion notification"""
    await send_task_update(
        task_id=task_id,
        status="completed",
        progress=100,
        message="Task completed successfully",
        result=result
    )

async def send_task_failure(task_id: str, error: str):
    """Send task failure notification"""
    await send_task_update(
        task_id=task_id,
        status="failed",
        progress=0,
        message=f"Task failed: {error}",
        result={"error": error}
    )

async def send_task_progress(task_id: str, progress: int, message: str):
    """Send task progress update"""
    await send_task_update(
        task_id=task_id,
        status="processing",
        progress=progress,
        message=message
    )

# User-specific notifications
async def send_user_notification(user_id: str, notification_type: str, data: Dict[str, Any]):
    """Send notification to specific user"""
    notification = {
        "type": notification_type,
        "data": data,
        "timestamp": asyncio.get_event_loop().time()
    }
    
    await sio.emit('user_notification', notification, room=f"user_{user_id}")

# System-wide notifications
async def send_system_notification(message: str, notification_type: str = "info"):
    """Send system-wide notification"""
    notification = {
        "type": "system",
        "notification_type": notification_type,
        "message": message,
        "timestamp": asyncio.get_event_loop().time()
    }
    
    await sio.emit('system_notification', notification)

# Health check functions
async def get_connection_stats() -> Dict[str, Any]:
    """Get WebSocket connection statistics"""
    current_time = asyncio.get_event_loop().time()
    
    # Calculate active connections
    active_count = len(active_connections)
    
    # Calculate connections by user
    user_connections = {}
    for sid, conn_data in active_connections.items():
        user_id = conn_data.get("user_id")
        if user_id:
            if user_id not in user_connections:
                user_connections[user_id] = 0
            user_connections[user_id] += 1
    
    return {
        "total_connections": active_count,
        "user_connections": user_connections,
        "active_connections": list(active_connections.keys())
    }

async def cleanup_inactive_connections(timeout: int = 300):
    """Clean up inactive connections (5 minutes timeout)"""
    current_time = asyncio.get_event_loop().time()
    inactive_sids = []
    
    for sid, conn_data in active_connections.items():
        last_activity = conn_data.get("last_activity", 0)
        if current_time - last_activity > timeout:
            inactive_sids.append(sid)
    
    for sid in inactive_sids:
        if sid in active_connections:
            del active_connections[sid]
        await sio.disconnect(sid)
    
    return len(inactive_sids)

# Periodic cleanup task
async def start_cleanup_task():
    """Start periodic cleanup of inactive connections"""
    while True:
        try:
            cleaned = await cleanup_inactive_connections()
            if cleaned > 0:
                print(f"Cleaned up {cleaned} inactive connections")
        except Exception as e:
            print(f"Cleanup task error: {e}")
        
        await asyncio.sleep(60)  # Run every minute

# Initialize cleanup task
cleanup_task = None

async def start_websocket_cleanup():
    """Start WebSocket cleanup task"""
    global cleanup_task
    if cleanup_task is None:
        cleanup_task = asyncio.create_task(start_cleanup_task())

async def stop_websocket_cleanup():
    """Stop WebSocket cleanup task"""
    global cleanup_task
    if cleanup_task:
        cleanup_task.cancel()
        cleanup_task = None

