# How to Import Projects from CSV

Import multiple projects at once using a CSV file. This guide will help you prepare your file correctly.

## Quick Start

1. **Download the example CSV file** using the "Download Example" button in the import dialog
2. Open the file in Excel, Google Sheets, or any spreadsheet application
3. Replace the example data with your own project information
4. Save the file as CSV format
5. Upload it using the Import button

## What You Need to Include

Your CSV file must have these **required** columns:

- **name** - The project name (required)
- **status** - Must be one of: `active`, `on-hold`, `completed`, or `cancelled`
- **category** - Must be one of: `design`, `development`, `marketing`, `support`, or `other`

You can also include these **optional** columns:

- **description** - A description of the project (can be left empty)
- **start_date** - When the project starts (format: `2024-01-15`)
- **end_date** - When the project ends (format: `2024-12-31`)
- **progress** - How complete the project is, from 0 to 100

## Date Format

All dates must be written as: **YYYY-MM-DD**

✅ **Correct formats:**
- `2024-01-15`
- `2024-12-31`

❌ **Incorrect formats:**
- `01/15/2024` (wrong format)
- `2024-1-5` (missing zeros)

## Status Options

Choose one of these status values:

- **active** - Project is currently in progress
- **on-hold** - Project is temporarily paused
- **completed** - Project has been finished
- **cancelled** - Project has been cancelled

## Category Options

Choose one of these category values:

- **design** - Design-related projects
- **development** - Software development projects
- **marketing** - Marketing and promotional projects
- **support** - Customer support projects
- **other** - Other types of projects

## Progress Values

- Enter a number between **0** and **100**
- This represents how complete your project is (0% = not started, 100% = finished)
- If you don't include this, it will default to 0

## Example

Here's what a simple CSV file looks like:

```csv
name,description,status,category,start_date,end_date,progress
Website Redesign,Complete redesign of company website,active,design,2024-01-15,2024-06-30,65
Mobile App Development,Build native mobile application,active,development,2024-02-01,2024-12-31,45
Marketing Campaign Q1,Launch Q1 marketing campaign,active,marketing,2024-01-10,2024-03-31,80
```

## Tips

- **Leave optional fields empty** if you don't have that information
- **End date must be after start date** - if you include both dates, make sure the end date comes after the start date
- **Projects are automatically assigned** to your team when imported
- **Column order doesn't matter** - as long as the column names match exactly

## Common Problems

**Problem:** Import fails with "missing required field"  
**Solution:** Make sure every row has a name, status, and category

**Problem:** Import fails with "invalid status"  
**Solution:** Check that your status values match exactly: `active`, `on-hold`, `completed`, or `cancelled`

**Problem:** Import fails with "invalid date format"  
**Solution:** Use the format `YYYY-MM-DD` (e.g., `2024-01-15`)

**Problem:** Import fails with "end date before start date"  
**Solution:** Make sure your end date is the same as or after your start date

**Problem:** Import fails with "invalid progress"  
**Solution:** Progress must be a whole number between 0 and 100 (no decimals)
