# SƠ ĐỒ LỚP HỆ THỐNG MVC (CLASS DIAGRAM)

Sơ đồ lớp thể hiện mối quan hệ cấu trúc giữa **Models (Eloquent)**, **Controllers**, **Services Nghiệp Vụ**, và **Middlewares** trong hệ thống Laravel.

```mermaid
classDiagram
    %% MODELS
    class User {
        +int id
        +string name
        +string email
        +string password
        +string role
        +isAdmin() bool
        +isManager() bool
        +isStreamer() bool
        +isClient() bool
        +streamerProfile()
        +managedStreamers()
        +clientBookings()
    }

    class StreamerProfile {
        +int id
        +int user_id
        +int manager_id
        +string stage_name
        +string category
        +decimal rate_per_hour
        +string status
        +getAvgViewersAttribute() int
        +getPeakViewersAttribute() int
        +getMonthlyStreamHoursAttribute() float
        +user()
        +manager()
        +bookings()
        +schedules()
        +metrics()
        +kpis()
    }

    class Booking {
        +int id
        +int client_id
        +int streamer_id
        +int manager_id
        +datetime start_time
        +datetime end_time
        +decimal budget
        +decimal commission_rate
        +string status
        +getDurationHoursAttribute() float
        +getAgencyCommissionAttribute() float
        +getStreamerNetIncomeAttribute() float
    }

    class Schedule {
        +int id
        +int streamer_id
        +string event_type
        +int reference_id
        +datetime start_time
        +datetime end_time
        +string title
    }

    class StreamMetric {
        +int id
        +int streamer_id
        +string platform
        +date stream_date
        +decimal duration_hours
        +int avg_viewers
        +int peak_viewers
        +int followers_gained
    }

    class Kpi {
        +int id
        +int streamer_id
        +string month_year
        +decimal target_hours
        +decimal target_revenue
        +decimal achieved_hours
        +decimal achieved_revenue
        +string status
        +getHoursProgressPercentageAttribute() int
        +getRevenueProgressPercentageAttribute() int
    }

    %% SERVICES
    class BookingCollisionService {
        +checkCollision(streamerId, startTime, endTime, ignoreId) array
        +approveAndLockSchedule(booking, managerId) array
        +rejectBooking(booking, managerId, reason) array
    }

    class CsvMetricsService {
        +importCsv(filePath) array
        +aggregateKpis(targets) int
    }

    class AiRecommendationService {
        +recommendTalents(userQuery) array
        #callGeminiApi(query, talents, key) array
        #callOpenAiApi(query, talents, key) array
        +fallbackRuleBasedRecommendation(query, talents) array
    }

    %% CONTROLLERS
    class BookingController {
        +index()
        +create()
        +store(Request, BookingCollisionService)
        +show(id, BookingCollisionService)
        +approve(id, BookingCollisionService)
        +reject(Request, id, BookingCollisionService)
    }

    class MetricsController {
        +showUploadForm()
        +uploadCsv(Request, CsvMetricsService)
        +downloadSample()
    }

    class AiChatController {
        +recommend(Request, AiRecommendationService)
    }

    %% RELATIONSHIPS
    User "1" --> "0..1" StreamerProfile : HasOne
    User "1" --> "0..*" StreamerProfile : Manages (1:N)
    User "1" --> "0..*" Booking : Creates as Client
    StreamerProfile "1" --> "0..*" Booking : Has Many
    StreamerProfile "1" --> "0..*" Schedule : Has Many
    StreamerProfile "1" --> "0..*" StreamMetric : Has Many
    StreamerProfile "1" --> "0..*" Kpi : Has Many
    Booking "1" --> "0..1" Schedule : Reference Link

    BookingController ..> BookingCollisionService : Injects
    BookingController ..> Booking : Manipulates
    MetricsController ..> CsvMetricsService : Injects
    MetricsController ..> StreamMetric : Manipulates
    AiChatController ..> AiRecommendationService : Injects
```
