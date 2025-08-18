# Claude's Understanding of webOS Catalog Backend

## Project Overview
This is the backend for the webOS App Museum II, which serves as a historical archive of legacy Palm/HP webOS mobile apps and games. The system provides search, browsing, and download capabilities for archived webOS applications.

## Architecture

### Core Components
- **WebService/**: Main API endpoints for app catalog operations
- **app/**: App search and redirect functionality  
- **author/**: Author profile pages and app listings by author
- **AppImages/**: App icon and screenshot hosting
- **AppPackages/**: IPK package file hosting
- **AuthorMetadata/**: Author profile data and icons

### Data Storage
- **masterAppData.json**: Primary app catalog data
- **newerAppData.json**: Recently added/updated apps
- **archivedAppData.json**: Legacy archived apps
- **missingAppData.json**: Apps that are missing/unavailable
- **outofdateAppData.json**: Apps needing updates

## Key Functionality

### Search System
- **getSearchResults.php**: Main search endpoint with rate limiting (60 req/hour)
- Supports both app and author searches
- Filters for adult content and LuneOS compatibility
- Uses fuzzy matching on titles and exact matching on IDs

### Rate Limiting
- **ratelimit.php**: Custom rate limiting implementation
- Tracks requests per IP in JSON files under `__rateLimit/` directory
- Default: 100 requests per hour, configurable per endpoint
- Includes IP detection for proxies/CDNs (Cloudflare support)
- Auto-cleanup of old rate limit files

### Internal Architecture Issue Fixed
**Problem**: `app/index.php` and `author/index.php` were making HTTP requests to `getSearchResults.php`, which caused them to be rate limited.

**Solution**: Modified both files to include search logic directly instead of making HTTP requests:
- Include `common.php` for `load_catalogs()` function
- Load catalog data directly from JSON files
- Implement search logic inline

## File Structure
```
/
├── WebService/           # API endpoints
│   ├── getSearchResults.php    # Main search (rate limited)
│   ├── getMuseumDetails.php    # App details
│   ├── getMuseumMaster.php     # Catalog listing
│   ├── getVendorIcon.php       # Author icons
│   ├── ratelimit.php           # Rate limiting system
│   └── config.php              # Configuration
├── app/index.php         # App search redirects (fixed rate limit issue)
├── author/index.php      # Author pages (fixed rate limit issue)
├── common.php            # Shared catalog loading functions
└── [catalog].json        # App data files
```

## Configuration
- Service configuration in `WebService/config.php`
- Supports multiple environments (service_host, image_host)
- Rate limiting configurable per endpoint

## Recent Changes
- Added rate limiting to prevent abuse
- Fixed internal HTTP request issues in app/ and author/ directories
- Rate limiting uses file-based storage for simplicity

## Testing Commands
- No specific test framework identified
- Manual testing recommended for search and rate limiting functionality