# Safety Guard
A flexible framework to management safety and security of the LLM applications.

## Components
1. Detector: Check whether the rules are triggered.
2. Client library: Collect data to detect and perform action based-on the detection result.
3. Manager: Manage and modify the rules.

## Model worker (legacy)
This model worker will check whether the chat history satisfy the safety principles.

### Usage
- The interface follows the LLM API of the LLM_Project
- Input: The chat history to check.
- Output: The original last message with the check result appended.