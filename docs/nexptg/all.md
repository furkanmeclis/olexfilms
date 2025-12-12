

NexPTG API Integration Documentation

**Version:** 1.0 based on Source PDF

## 1. Connection Overview

The NexPTG application synchronizes data to a client server using a **RESTful Web Service**. The server acts as a receiver for data collected by the mobile application.

* **Protocol:** HTTPS
* **Method:** `POST`
* **Data Format:** `application/json`
* **Encoding:** UTF-8
* **Synchronization Behavior:** Incremental (only new data saved since the last synchronization is sent).

### URL Endpoint

You must implement an endpoint on your server (e.g., `https://your-domain.com/api/sync`). The application will send the `POST` request to this URL.

### Authentication

The API supports **Basic Authentication**.

* **Mechanism:** Standard HTTP Basic Auth (Base64 encoded `username:password` in the Header).
* **Implementation:** If your server requires auth, the NexPTG app user will be prompted to enter credentials in the "Synchronization" tab.

---

## 2. Response Status Codes

Your server must return standard HTTP status codes to indicate the result of the synchronization attempt.

| Code              | Status       | Description                                |
| :---------------- | :----------- | :----------------------------------------- |
| **200**     | OK           | Synchronization was successful.            |
| **400**     | Bad Request  | Error in the request syntax or validation. |
| **403**     | Forbidden    | Authorization error (Invalid credentials). |
| **404**     | Not Found    | API address is invalid/unreachable.        |
| **500-599** | Server Error | Internal server error processing the data. |

---

## 3. JSON Payload Structure

The `POST` body contains a root JSON object wrapping the data. The root key is always `"data"`. Inside, there are two main arrays: `history` and `reports`.

### 3.1 Root Object

```json
{
  "data": {
    "history": [ ... ],
    "reports": [ ... ]
  }
}
```

### 3.2 "history" Array

Contains the raw measurement history logs independent of specific reports.

| Field    | Data Type | Description                                        |
| :------- | :-------- | :------------------------------------------------- |
| `id`   | Integer   | Unique identifier for the history group.           |
| `name` | String    | Name of the history group (e.g., "General").       |
| `data` | Array     | List of individual measurement points (see below). |

**Inside `history.data` array:**

| Field              | Data Type | Description                                                             |
| :----------------- | :-------- | :---------------------------------------------------------------------- |
| `value`          | Integer   | Thickness measurement value (in microns `μm`).                       |
| `interpretation` | Integer   | Evaluation code (e.g., 1=Lacquer, 2=Original, 3=Second Paint, 4=Putty). |
| `type`           | String    | Substrate type (e.g.,`"Zn"`, `"Al"`, `"Fe"`).                     |
| `date`           | Timestamp | Unix Epoch timestamp (seconds).                                         |

### 3.3 "reports" Array

Contains detailed vehicle inspection reports. Each object in this array represents a full report containing vehicle metadata, paint measurements (`data` and `dataInside`), and tire data.

#### A. Report Metadata Fields

| Field                  | Data Type | Description                               |
| :--------------------- | :-------- | :---------------------------------------- |
| `id`                 | Integer   | Report ID.                                |
| `name`               | String    | Report Name (e.g., "Report").             |
| `date`               | Timestamp | Creation date of the report (Unix Epoch). |
| `calibrationDate`    | Timestamp | Date of device calibration (Unix Epoch).  |
| `deviceSerialNumber` | String    | Serial number of the NexPTG device.       |
| `model`              | String    | Vehicle Model (e.g., "Mondeo").           |
| `brand`              | String    | Vehicle Brand (e.g., "Ford").             |
| `typeOfBody`         | String    | Body type (e.g., "SEDAN").                |
| `capacity`           | String    | Engine capacity.                          |
| `power`              | String    | Engine power.                             |
| `vin`                | String    | Vehicle Identification Number.            |
| `fuelType`           | String    | Fuel type (e.g., "Diesel").               |
| `year`               | String    | Manufacturing year.                       |
| `unitOfMeasure`      | String    | Unit used (e.g., "μm").                  |
| `extraFields`        | Array     | Custom fields (if any).                   |
| `comment`            | String    | User comments added to the report.        |

#### B. Report Measurement Objects (`data` and `dataInside`)

The report contains two measurement arrays:

1. **`data`**: External body measurements.
2. **`dataInside`**: Internal structural measurements.

Both arrays share the same structure based on `placeId`.

**Structure:**

* **`placeId`**: String (Enum: `"left"`, `"right"`, `"top"`, `"back"`).
* **`data`**: Array of components within that place.
  * **`type`**: String ID of the specific part (e.g., `"LEFT_FRONT_DOOR"`, `"HOOD"`, `"ROOF"`, `"LEFT_PILLAR"`).
  * **`values`**: Array of measurements on that part.

**Measurement Value Object:**

| Field              | Data Type  | Description                                                                                       |
| :----------------- | :--------- | :------------------------------------------------------------------------------------------------ |
| `value`          | String/Int | Measurement value (Note: Sample shows string `"110"` or int `110`, usually parsed as number). |
| `interpretation` | Integer    | Result code (e.g., 1=OK, 3=Repainted).                                                            |
| `type`           | String     | Substrate type (e.g.,`"Fe"`, `"Al"`, `"Fe + Zn"`).                                          |
| `timestamp`      | Timestamp  | Time of specific measurement point.                                                               |
| `position`       | Integer    | Ordering index of the measurement on the component.                                               |

#### C. "tires" Array

Located inside the report object.

| Field        | Data Type | Description                                            |
| :----------- | :-------- | :----------------------------------------------------- |
| `width`    | String    | Tire width (e.g.,`"140"`).                           |
| `profile`  | String    | Tire profile (e.g.,`"55"`).                          |
| `diameter` | String    | Rim diameter (e.g.,`"18"`).                          |
| `maker`    | String    | Tire manufacturer (e.g.,`"Apollo"`).                 |
| `season`   | String    | Season type (e.g.,`"Summer"`, `"Winter"`).         |
| `section`  | String    | Position on car (e.g.,`"Left front"`).               |
| `value1`   | String    | Tread depth measurement 1 (e.g.,`"1"` or `"1.2"`). |
| `value2`   | String    | Tread depth measurement 2 (e.g.,`"2"` or `"3"`).   |

---

## 4. Full JSON Payload Example

Use this exact structure for your implementation testing.

```json
{
  "data": {
    "history": [
      {
        "id": 1,
        "name": "General",
        "data": [
          { "value": 656, "interpretation": 4, "type": "Zn", "date": 1702629359 },
          { "value": 233, "interpretation": 2, "type": "Al", "date": 1702629357 },
          { "value": 105, "interpretation": 1, "type": "Fe", "date": 1702629353 }
        ]
      }
    ],
    "reports": [
      {
        "id": 56,
        "name": "Report",
        "date": 1702629560,
        "calibrationDate": 1701264763,
        "deviceSerialNumber": "18416 Professional",
        "model": "Mondeo",
        "brand": "Ford",
        "typeOfBody": "SEDAN",
        "capacity": "105",
        "power": "1999",
        "vin": "",
        "fuelType": "Diesel",
        "year": "2010",
        "unitOfMeasure": "μm",
        "extraFields": [],
        "comment": "Comment in report",
        "data": [
          {
            "placeId": "left",
            "data": [
              {
                "type": "LEFT_FRONT_FENDER",
                "values": [
                  { "value": "375", "interpretation": 3, "type": "Al", "timestamp": 1702629432, "position": 1 }
                ]
              },
              {
                "type": "LEFT_FRONT_DOOR",
                "values": [
                  { "value": "110", "interpretation": 1, "type": "Al", "timestamp": 1702629434, "position": 1 },
                  { "value": "108", "interpretation": 1, "type": "Al", "timestamp": 1702629435, "position": 2 }
                ]
              }
            ]
          },
          {
            "placeId": "right",
            "data": [
              {
                "type": "RIGHT_FRONT_FENDER",
                "values": [
                  { "value": "106", "interpretation": 1, "type": "Fe", "timestamp": 1702629472, "position": 1 }
                ]
              }
            ]
          }
        ],
        "dataInside": [
          {
            "placeId": "top",
            "data": [
              {
                "type": "ENGINE_COMPARTMENT",
                "values": [
                  { "value": "105", "interpretation": 2, "type": "Fe", "timestamp": 1702629480, "position": 1 }
                ]
              }
            ]
          }
        ],
        "tires": [
          {
            "width": "140",
            "profile": "55",
            "diameter": "18",
            "maker": "Apollo",
            "season": "Summer",
            "section": "Left front",
            "value1": "1",
            "value2": "2"
          },
          {
            "width": "150",
            "profile": "60",
            "diameter": "16",
            "maker": "Barum",
            "season": "Winter",
            "section": "Right front",
            "value1": "5",
            "value2": "2"
          }
        ]
      }
    ]
  }
}
```

## 5. Implementation Checklist

1. [ ] **Endpoint:** Create a `POST` route that accepts raw JSON.
2. [ ] **Headers:** Ensure the endpoint accepts `Content-Type: application/json`.
3. [ ] **Auth:** If desired, configure Basic Authentication middleware.
4. [ ] **Parser:** Map the JSON fields exactly as defined above (pay attention to `dataInside` vs `data`).
5. [ ] **Logic:** Implement logic to check `id` or timestamps to avoid duplicating records (since the app sends data incrementally, but redundancy checks are best practice).
6. [ ] **Response:** Ensure your code returns HTTP 200 upon successful receipt.
