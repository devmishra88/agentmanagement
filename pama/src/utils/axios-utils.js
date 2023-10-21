import axios from "axios";

const client = axios.create({
  baseURL: 'https://pama.dealcorner.in/api',
  // headers: {
  //   'Content-Type': 'application/json',
  // },
});

export const request = async ({ ...options }) => {
  /*client.defaults.headers.common.Authorization = `Bearer token`;*/
  const onSuccess = (response) => response;
  const onError = (error) => {
    //optionally catch errors and add additional logging here
    return error;
  };

  try {
    const response = await client(options);
    return onSuccess(response);
  } catch (error) {
    return onError(error);
  }
};

export const request_bak = async ({ method, url, data }) => {
  try {
    const response = await client({
      method, // HTTP method (e.g., 'GET', 'POST', 'PUT', 'DELETE', etc.)
      url, // Relative or absolute URL
      data, // Data to be sent in the request body (for POST or PUT requests)
    });
    
    // Check for a successful response status (e.g., 200 for OK)
    if (response.status === 200) {
      // Successful response, return the data
      return response.data;
    } else {
      // Handle other response status codes as needed
      throw new Error(`'Request failed with status:${response.status}`);
    }
  } catch (error) {
    // Handle any request or network errors
    throw error;
  }
};

export const objectToFormData = (obj) => {
  const formData = new FormData();

  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      formData.append(key, obj[key]);
    }
  }

  return formData;
};