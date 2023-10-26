import axios from "axios";

const client = axios.create({
  baseURL: process.env.REACT_APP_API_URL,
  // headers: {
  //   'Content-Type': 'application/json',
  // },
});

export const request = async ({ ...options }) => {
  const onSuccess = (response) => response;
  const onError = (error) => {
    return error;
  };

  try {
    const response = await client(options);
    return onSuccess(response);
  } catch (error) {
    return onError(error);
  }
};

export const secureRequest = async ({ ...options }) => {
  const token = JSON.parse(localStorage.getItem(`${process.env.REACT_APP_STORAGE_KEY}`)) || null;
  if(token)
  {
    client.defaults.headers.common.Authorization = token.accesstoken;
  }
  const onSuccess = (response) => response;
  const onError = (error) => {
    return error;
  };

  try {
    const response = await client(options);
    return onSuccess(response);
  } catch (error) {
    return onError(error);
  }
};

export const objectToFormData = (obj) => {
  const formData = new FormData();

  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      const value = obj[key];
      formData.append(key, typeof value === 'boolean' ? +value : value);
    }
  }

  return formData;
};

export const request_bak = async ({ method, url, data }) => {
  try {
    const response = await client({
      method,
      url,
      data,
    });
    
    if (response.status === 200) {
      return response.data;
    } else {
      throw new Error(`'Request failed with status:${response.status}`);
    }
  } catch (error) {
    throw error;
  }
};
