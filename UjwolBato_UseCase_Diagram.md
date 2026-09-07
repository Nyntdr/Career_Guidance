# UjwolBato - AI Career Guidance with ATS & Skill Analysis
## Use Case Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  UjwolBato - AI Career Guidance with ATS & Skill Analysis  │
│                                                             │
│                      ┌──────────────┐                       │
│                      │   Register   │                       │
│                      └──────────────┘                       │
│                            │                                │
│                      ┌──────────────┐                       │
│                      │    Login     │                       │
│                      └──────────────┘                       │
│                            │                                │
│        ┌───────────────────┼───────────────────┐            │
│        │                   │                   │            │
│   ┌────────────────┐  ┌────────────────┐ ┌────────────┐   │
│   │ Upload Resume  │  │ View Gap Skills│ │ Profile    │   │
│   └────────────────┘  └────────────────┘ └────────────┘   │
│        │                   │                   │            │
│   ┌────────────────┐  ┌────────────────┐ ┌────────────┐   │
│   │ ATS Scoring    │  │ Skill Analysis │ │ Saved Jobs │   │
│   └────────────────┘  └────────────────┘ └────────────┘   │
│        │                   │                   │            │
│   ┌────────────────┐  ┌────────────────┐ ┌────────────┐   │
│   │Career Path     │  │ Learning Path  │ │Export Data │   │
│   │Recommendation  │  │Recommendations │ └────────────┘   │
│   └────────────────┘  └────────────────┘                   │
│        │                   │                                │
└────────┼───────────────────┼────────────────────────────────┘
         │                   │
    ┌────┴─┐           ┌─────┴────┐
    │Admin │           │   User   │
    └──────┘           └──────────┘
         │                   │
    ┌────────────────┐  ┌────────────────┐
    │ Manage Career  │  │Provide Feedback│
    │ Paths & Skills │  │ on Results     │
    └────────────────┘  └────────────────┘
         │                   │
    ┌────────────────┐  ┌────────────────┐
    │Monitor System  │  │Refine Career   │
    │Usage & Analytics    Guidance       │
    └────────────────┘  └────────────────┘
```

## Key Use Cases

### User Actions:
- **Register** - Create new account
- **Login** - Authenticate and access system
- **Upload Resume** - Submit resume for analysis
- **ATS Scoring** - Get resume matching score against job requirements
- **View Gap Skills** - Identify missing skills for target roles
- **Skill Analysis** - Detailed breakdown of technical & soft skills
- **Career Path Recommendation** - AI-suggested career trajectories
- **Learning Path Recommendations** - Personalized course and skill development suggestions
- **Profile Management** - Update personal and professional information
- **Saved Jobs** - Bookmark and track job opportunities
- **Export Data** - Download analysis reports and recommendations
- **Provide Feedback** - Rate and comment on guidance quality

### Admin Actions:
- **Manage Career Paths & Skills** - Update skill hierarchies and career progressions
- **Monitor System Usage & Analytics** - Track user engagement and system performance
- **Handle System Administration** - Configure AI models and system settings
