import sys
import pickle
import pandas as pd
import os
import json

# Check installed packages
try:
    import pandas as pd
    import pickle
except ImportError as e:
    print("Error importing packages:", e)

# Load the saved model
try:
    model_path = os.path.join(os.path.dirname(__file__), 'xgboost_for_forecasting.pkl')
    with open(model_path, 'rb') as file:
        model = pickle.load(file)
except FileNotFoundError:
    print(f"Error: Model file not found at {model_path}")
    sys.exit(1)
except Exception as e:
    print(f"Error loading model: {e}")
    sys.exit(1)

# Get the inputs from the command line arguments
start_date = sys.argv[1]
unit = sys.argv[2]  # 'day', 'month', 'year'
count = int(sys.argv[3])

# Define the create_features function
def create_features(df):
    df = df.copy()
    df['dayofweek'] = df.index.dayofweek
    df['quarter'] = df.index.quarter
    df['month'] = df.index.month
    df['year'] = df.index.year
    df['dayofyear'] = df.index.dayofyear
    df['weekofyear'] = df.index.isocalendar().week
    return df

# Generate a date range based on the inputs
if unit == 'day':
    dates = pd.date_range(start=start_date, periods=count, freq='D')
elif unit == 'month':
    dates = pd.date_range(start=start_date, periods=count*30, freq='D')
elif unit == 'year':
    dates = pd.date_range(start=start_date, periods=count*365, freq='D')
else:
    raise ValueError("Unit must be 'day', 'month', or 'year'")

# Convert dates to DataFrame
df = pd.DataFrame(index=dates)
df = create_features(df)

FEATURES = [
    'dayofweek',
    'quarter',
    'month',
    'year',
    'dayofyear',
    'weekofyear',
]

X = df[FEATURES]
predictions = model.predict(X)

# Collect predictions into a dictionary
results = {str(date.date()): int(prediction) for date, prediction in zip(dates, predictions)}

# Prepare the final result structure
final_result = {
    "message": "Predictions generated successfully",
    "data": [results]  # Wrap results in a list to match the desired output structure
}

# Convert the final result to a JSON string
results_json = json.dumps(final_result)

# Print the JSON string
print(results_json)
